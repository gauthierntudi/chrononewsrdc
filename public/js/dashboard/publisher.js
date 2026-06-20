(() => {
    const U = window.DashboardUtils;
    const cfg = U?.cfg || {};
    const pub = window.CHRONONEWS_PUBLISHER || {};

    let currentArticleId = pub.articleId || null;
    let quillEditor = null;
    let publishDatePicker = null;
    let blocks = [];
    let currentBlockIndex = null;
    let coverFiles = [];
    let blockImageFile = null;
    let saving = false;
    let aiUI = null;

    document.addEventListener('DOMContentLoaded', async () => {
        U?.showLoader('Chargement de l\'éditeur...');
        try {
            initDatePicker();
            initQuill();
            bindEvents();
            await initAiAssistant();
            if (currentArticleId) {
                await loadArticle(currentArticleId);
            } else if (publishDatePicker) {
                publishDatePicker.setDate(new Date());
            }
            U?.refreshIcons?.(document);
        } catch (error) {
            U?.showToast?.(error.message || 'Erreur de chargement', 'error');
        } finally {
            U?.hideLoader?.();
        }
    });

    function initDatePicker() {
        const field = document.querySelector('.publisher-date-field');
        const input = document.getElementById('articlePublishDate');
        if (!field || !input || typeof flatpickr === 'undefined') return;

        publishDatePicker = flatpickr(field, {
            enableTime: true,
            time_24hr: true,
            dateFormat: 'Y-m-d H:i',
            altInput: true,
            altFormat: 'j F Y à H:i',
            locale: flatpickr.l10ns?.fr || 'fr',
            defaultDate: new Date(),
            minuteIncrement: 1,
            clickOpens: true,
            allowInput: false,
            wrap: true,
        });
    }

    function initQuill() {
        if (typeof Quill === 'undefined') return;

        quillEditor = new Quill('#blockContentEditor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['blockquote', 'link'],
                    ['clean'],
                ],
            },
            placeholder: 'Écrivez votre contenu ici...',
        });
    }

    async function initAiAssistant() {
        if (typeof initAICorrector !== 'function') return;

        const { ui, enabled } = await initAICorrector(cfg.apiBase || '/api/v1');
        if (!enabled || !ui) return;

        aiUI = ui;
        ui.attachToInput(document.getElementById('articleTitle'));
        ui.attachToTextarea(document.getElementById('articleDescription'));
        ui.attachToInput(document.getElementById('coverCaption'));
    }

    function attachAiToBlockFields() {
        if (!aiUI) return;
        const blockTitle = document.getElementById('blockTitle');
        if (blockTitle && !aiUI.attachedFields.has(blockTitle)) {
            aiUI.attachToInput(blockTitle);
        }
        if (quillEditor) {
            aiUI.attachToQuill(quillEditor);
        }
    }

    function bindEvents() {
        document.getElementById('publisherBackBtn')?.addEventListener('click', goBack);
        document.getElementById('publisherSaveBtn')?.addEventListener('click', saveArticle);
        document.getElementById('addBlockBtn')?.addEventListener('click', () => showBlockEditor());
        document.getElementById('cancelBlockBtn')?.addEventListener('click', cancelBlockEdit);
        document.getElementById('saveBlockBtn')?.addEventListener('click', saveBlock);
        document.getElementById('articleIsPaid')?.addEventListener('change', togglePriceField);

        const coverZone = document.getElementById('coverUploadZone');
        const coverInput = document.getElementById('coverInput');
        coverZone?.addEventListener('click', () => coverInput?.click());
        coverZone?.addEventListener('dragover', (e) => { e.preventDefault(); });
        coverZone?.addEventListener('drop', (e) => {
            e.preventDefault();
            const files = Array.from(e.dataTransfer.files).filter((f) => f.type.startsWith('image/'));
            if (files.length) handleCoverSelect(files);
        });
        coverInput?.addEventListener('change', (e) => {
            const files = Array.from(e.target.files || []);
            if (files.length) handleCoverSelect(files);
        });

        const coverPreview = document.getElementById('coverPreview');
        coverPreview?.addEventListener('click', (e) => {
            const btn = e.target.closest('.remove-cover-btn');
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();
            const idx = Number(btn.dataset.index);
            if (Number.isNaN(idx)) return;
            coverFiles = coverFiles.filter((_, i) => i !== idx);
            if (coverInput) coverInput.value = '';
            renderCoverPreviews();
        });

        const blockZone = document.getElementById('blockImageUploadZone');
        const blockInput = document.getElementById('blockImageInput');
        blockZone?.addEventListener('click', () => blockInput?.click());
        blockInput?.addEventListener('change', (e) => {
            const file = e.target.files?.[0];
            if (file) handleBlockImageSelect(file);
        });

        bindBlocksListEvents();
    }

    function bindBlocksListEvents() {
        const list = document.getElementById('blocksList');
        if (!list || list.dataset.bound === '1') return;
        list.dataset.bound = '1';

        list.addEventListener('click', async (e) => {
            const editBtn = e.target.closest('[data-edit-block]');
            if (editBtn) {
                showBlockEditor(Number(editBtn.dataset.editBlock));
                return;
            }

            const deleteBtn = e.target.closest('[data-delete-block]');
            if (deleteBtn) {
                const index = Number(deleteBtn.dataset.deleteBlock);
                const confirmed = await U.confirm('Supprimer ce bloc ?');
                if (!confirmed) return;
                blocks.splice(index, 1);
                renderBlocks();
                return;
            }

            const moveBtn = e.target.closest('[data-move-block]');
            if (moveBtn) {
                moveBlock(Number(moveBtn.dataset.index), Number(moveBtn.dataset.direction));
            }
        });
    }

    function togglePriceField() {
        const container = document.getElementById('priceFieldContainer');
        const checked = document.getElementById('articleIsPaid')?.checked;
        if (container) container.style.display = checked ? 'block' : 'none';
    }

    function handleCoverSelect(files) {
        coverFiles = [...coverFiles, ...files];
        renderCoverPreviews();
    }

    function renderCoverPreviewItem(file, index) {
        const wrap = document.createElement('div');
        wrap.className = 'cover-preview-item';

        if (typeof file === 'string') {
            wrap.innerHTML = `
                <img src="${U.mediaUrl(file)}" alt="Couverture">
                <button type="button" class="remove-cover-btn" data-index="${index}" aria-label="Supprimer">&times;</button>
            `;
            return wrap;
        }

        wrap.innerHTML = `
            <div class="cover-preview-loading">Chargement…</div>
            <button type="button" class="remove-cover-btn" data-index="${index}" aria-label="Supprimer">&times;</button>
        `;

        const reader = new FileReader();
        reader.onload = (e) => {
            const loading = wrap.querySelector('.cover-preview-loading');
            if (loading) {
                loading.outerHTML = `<img src="${e.target.result}" alt="Couverture">`;
            }
        };
        reader.readAsDataURL(file);

        return wrap;
    }

    function renderCoverPreviews() {
        const zone = document.getElementById('coverUploadZone');
        const placeholder = document.getElementById('coverPlaceholder');
        const preview = document.getElementById('coverPreview');

        if (!coverFiles.length) {
            zone?.classList.remove('has-image');
            if (placeholder) placeholder.style.display = 'block';
            if (preview) {
                preview.style.display = 'none';
                preview.innerHTML = '';
            }
            return;
        }

        zone?.classList.add('has-image');
        if (placeholder) placeholder.style.display = 'none';
        if (!preview) return;

        preview.innerHTML = '';
        preview.style.display = 'flex';

        coverFiles.forEach((file, index) => {
            preview.appendChild(renderCoverPreviewItem(file, index));
        });
    }

    function handleBlockImageSelect(file) {
        if (!file?.type?.startsWith('image/')) return;
        blockImageFile = file;

        const placeholder = document.getElementById('blockImagePlaceholder');
        const preview = document.getElementById('blockImagePreview');
        const zone = document.getElementById('blockImageUploadZone');

        const reader = new FileReader();
        reader.onload = (e) => {
            if (placeholder) placeholder.style.display = 'none';
            zone?.classList.add('has-image');
            if (preview) {
                preview.style.display = 'block';
                preview.innerHTML = `
                    <div class="cover-preview-item" style="width:200px;height:140px;">
                        <img src="${e.target.result}" alt="Aperçu bloc">
                        <button type="button" class="remove-cover-btn" id="removeBlockImageBtn" aria-label="Supprimer">&times;</button>
                    </div>
                `;
                document.getElementById('removeBlockImageBtn')?.addEventListener('click', (ev) => {
                    ev.stopPropagation();
                    removeBlockImage();
                });
            }
        };
        reader.readAsDataURL(file);
    }

    function removeBlockImage() {
        blockImageFile = null;
        document.getElementById('blockImageInput').value = '';
        document.getElementById('blockImageUploadZone')?.classList.remove('has-image');
        const placeholder = document.getElementById('blockImagePlaceholder');
        const preview = document.getElementById('blockImagePreview');
        if (placeholder) placeholder.style.display = 'block';
        if (preview) {
            preview.style.display = 'none';
            preview.innerHTML = '';
        }
    }

    function showBlockEditor(index = null) {
        const editIndex = typeof index === 'number' && Number.isFinite(index) ? index : null;
        currentBlockIndex = editIndex;
        document.getElementById('editorTitle').textContent = editIndex !== null ? 'Modifier le bloc' : 'Nouveau bloc';
        document.getElementById('saveBlockText').textContent = editIndex !== null ? 'Enregistrer le bloc' : 'Ajouter le bloc';
        resetBlockForm();

        if (editIndex !== null && blocks[editIndex]) {
            const block = blocks[editIndex];
            document.getElementById('blockTitle').value = block.title || '';
            quillEditor?.clipboard.dangerouslyPasteHTML(block.content || '');
            document.getElementById('blockImageCaption').value = block.caption || '';
            document.getElementById('blockVideoUrl').value = block.videos || '';
            if (block.cover) {
                blockImageFile = block.cover;
                const preview = document.getElementById('blockImagePreview');
                const placeholder = document.getElementById('blockImagePlaceholder');
                document.getElementById('blockImageUploadZone')?.classList.add('has-image');
                if (placeholder) placeholder.style.display = 'none';
                if (preview) {
                    const imageSrc = block.cover instanceof File
                        ? URL.createObjectURL(block.cover)
                        : U.mediaUrl(block.cover);
                    preview.style.display = 'block';
                    preview.innerHTML = `
                        <div class="cover-preview-item" style="width:200px;height:140px;">
                            <img src="${imageSrc}" alt="Aperçu bloc">
                            <button type="button" class="remove-cover-btn" id="removeBlockImageBtn" aria-label="Supprimer">&times;</button>
                        </div>
                    `;
                    document.getElementById('removeBlockImageBtn')?.addEventListener('click', (ev) => {
                        ev.stopPropagation();
                        removeBlockImage();
                    });
                }
            }
        }

        document.getElementById('blockEditor').style.display = 'block';
        document.getElementById('blockEditor').scrollIntoView({ behavior: 'smooth', block: 'start' });

        if (editIndex !== null) {
            requestAnimationFrame(() => highlightBlockCard(editIndex));
        }

        setTimeout(attachAiToBlockFields, 100);
    }

    function resetBlockForm() {
        document.getElementById('blockTitle').value = '';
        quillEditor?.setText('');
        document.getElementById('blockImageCaption').value = '';
        document.getElementById('blockVideoUrl').value = '';
        removeBlockImage();
    }

    function cancelBlockEdit() {
        document.getElementById('blockEditor').style.display = 'none';
        currentBlockIndex = null;
        resetBlockForm();
    }

    function saveBlock() {
        const title = document.getElementById('blockTitle').value.trim();
        const textContent = quillEditor?.getText()?.trim() || '';
        const hasContent = textContent !== '' && textContent !== '\n';
        const htmlContent = hasContent ? quillEditor.root.innerHTML : '';
        const videoUrl = document.getElementById('blockVideoUrl').value.trim();
        const caption = document.getElementById('blockImageCaption').value.trim();

        if (!title && !hasContent && !blockImageFile && !videoUrl) {
            U.showToast('Veuillez remplir au moins un champ du bloc', 'warning');
            return;
        }

        if (videoUrl && !videoUrl.includes('youtube.com') && !videoUrl.includes('youtu.be')) {
            U.showToast('Seules les URL YouTube sont acceptées', 'warning');
            return;
        }

        const blockData = {
            title: title || null,
            content: hasContent ? htmlContent : null,
            cover: blockImageFile || null,
            caption: caption || null,
            videos: videoUrl || null,
        };

        const isEdit = typeof currentBlockIndex === 'number' && currentBlockIndex >= 0;

        if (isEdit) {
            if (blocks[currentBlockIndex]?.id) blockData.id = blocks[currentBlockIndex].id;
            if (!blockImageFile && blocks[currentBlockIndex]?.cover) {
                blockData.cover = blocks[currentBlockIndex].cover;
            }
            blocks[currentBlockIndex] = blockData;
        } else {
            blocks.push(blockData);
        }

        const savedIndex = isEdit ? currentBlockIndex : blocks.length - 1;
        const wasEdit = isEdit;
        renderBlocks();
        cancelBlockEdit();
        highlightBlockCard(savedIndex);
        U.showToast(wasEdit ? 'Bloc modifié' : 'Bloc ajouté à l\'article', 'success');
    }

    function moveBlock(index, direction) {
        const newIndex = index + direction;
        if (newIndex < 0 || newIndex >= blocks.length) return;

        const temp = blocks[index];
        blocks[index] = blocks[newIndex];
        blocks[newIndex] = temp;
        renderBlocks();
        highlightBlockCard(newIndex);
    }

    function getBlockContentTypes(block) {
        const types = [];
        if (block.content) types.push('Texte');
        if (block.cover) types.push('Image');
        if (block.videos) types.push('Vidéo');
        return types.join(' + ') || 'Bloc';
    }

    function getBlockPreviewText(block) {
        const parts = [];

        if (block.content) {
            const temp = document.createElement('div');
            temp.innerHTML = block.content;
            const text = (temp.textContent || '').trim();
            if (text) parts.push(text.substring(0, 120));
        }

        if (block.caption) {
            parts.push(`Légende : ${block.caption}`);
        }

        if (block.videos) {
            parts.push(`Vidéo : ${block.videos.substring(0, 48)}${block.videos.length > 48 ? '…' : ''}`);
        }

        return parts.join(' • ') || 'Contenu du bloc';
    }

    function getBlockImageSrc(block) {
        if (!block.cover) return '';

        if (block.cover instanceof File) {
            return URL.createObjectURL(block.cover);
        }

        if (typeof block.cover === 'string') {
            return U.mediaUrl(block.cover);
        }

        return '';
    }

    function highlightBlockCard(index) {
        const list = document.getElementById('blocksList');
        const card = list?.querySelector(`[data-block-index="${index}"]`);
        if (!card) return;

        list.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        card.classList.remove('block-item--highlight');
        card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        card.classList.add('block-item--highlight');
        window.setTimeout(() => card.classList.remove('block-item--highlight'), 2200);
    }

    function renderBlocks() {
        const list = document.getElementById('blocksList');
        if (!list) return;

        if (!blocks.length) {
            list.innerHTML = `
                <div class="empty-state">
                    ${U.icon('box')}
                    <p>Aucun bloc ajouté. Cliquez sur « Ajouter un bloc » pour commencer.</p>
                </div>
            `;
            U.refreshIcons(list);
            return;
        }

        list.innerHTML = blocks.map((block, index) => {
            const imageSrc = getBlockImageSrc(block);
            const imageHtml = imageSrc
                ? `<img src="${imageSrc}" class="block-item-image" alt="Aperçu du bloc">`
                : '';

            return `
                <article class="block-item" data-block-index="${index}">
                    <div class="block-item-order">
                        <button type="button" class="block-item-order-btn" data-move-block data-index="${index}" data-direction="-1" ${index === 0 ? 'disabled' : ''} aria-label="Monter le bloc">
                            ${U.icon('chevron-up')}
                        </button>
                        <button type="button" class="block-item-order-btn" data-move-block data-index="${index}" data-direction="1" ${index === blocks.length - 1 ? 'disabled' : ''} aria-label="Descendre le bloc">
                            ${U.icon('chevron-down')}
                        </button>
                    </div>
                    <div class="block-item-content">
                        <div class="block-item-type">${U.escapeHtml(getBlockContentTypes(block))}</div>
                        <div class="block-item-title">${U.escapeHtml(block.title || `Bloc ${index + 1}`)}</div>
                        <div class="block-item-preview">${U.escapeHtml(getBlockPreviewText(block))}</div>
                    </div>
                    ${imageHtml}
                    <div class="block-item-actions">
                        <button type="button" class="block-item-action-btn block-item-action-btn--edit" data-edit-block="${index}" aria-label="Modifier le bloc" title="Modifier">
                            ${U.icon('pencil')}
                        </button>
                        <button type="button" class="block-item-action-btn block-item-action-btn--delete" data-delete-block="${index}" aria-label="Supprimer le bloc" title="Supprimer">
                            ${U.icon('trash-2')}
                        </button>
                    </div>
                </article>
            `;
        }).join('');

        U.refreshIcons(list);
    }

    async function loadArticle(id) {
        const data = await U.api(`${cfg.apiBase}/articles/${id}`);
        const article = data.article;
        if (!article) throw new Error('Article non trouvé');

        currentArticleId = article.id;
        document.getElementById('pageTitle').textContent = 'Modifier l\'article';
        document.getElementById('articleTitle').value = decodeHtml(article.title || '');
        document.getElementById('articleDescription').value = article.content || '';
        document.getElementById('articleCategory').value = U.normalizeCategory(article.category || article.categorie || '');

        const dateValue = article.published_at || article.created_at;
        if (dateValue && publishDatePicker) {
            publishDatePicker.setDate(String(dateValue).replace(' ', 'T'), false);
        }

        if (article.caption) document.getElementById('coverCaption').value = decodeHtml(article.caption);
        if (article.videos) document.getElementById('articleVideo').value = article.videos;
        if (article.is_featured || article.alaune === 'YES') {
            document.getElementById('articleFeatured').checked = true;
        }
        if (article.is_paid) {
            document.getElementById('articleIsPaid').checked = true;
            togglePriceField();
            if (article.price) document.getElementById('articlePrice').value = article.price;
        }

        if (article.cover) {
            coverFiles = String(article.cover).split(',').map((s) => s.trim()).filter(Boolean);
            renderCoverPreviews();
        }

        const blocksData = await U.api(`${cfg.apiBase}/articles/${id}/blocks`);
        blocks = (blocksData.blocks || []).map((block) => ({
            id: block.id,
            title: block.title || null,
            content: block.content || null,
            cover: block.cover || null,
            caption: block.caption || null,
            videos: block.videos || null,
        }));
        renderBlocks();
    }

    async function saveArticle() {
        if (saving) return;

        const title = document.getElementById('articleTitle').value.trim();
        const content = document.getElementById('articleDescription').value.trim();
        const category = document.getElementById('articleCategory').value;
        const publishDate = publishDatePicker?.input?.value
            || document.getElementById('articlePublishDate')?.value
            || '';
        const caption = document.getElementById('coverCaption').value.trim();
        const videos = document.getElementById('articleVideo').value.trim();
        const isFeatured = document.getElementById('articleFeatured').checked;
        const isPaid = document.getElementById('articleIsPaid').checked;
        const price = isPaid ? document.getElementById('articlePrice').value : null;

        if (!title) return U.showToast('Le titre est requis', 'warning');
        if (!content) return U.showToast('La description est requise', 'warning');
        if (!category) return U.showToast('La catégorie est requise', 'warning');
        if (!publishDate) return U.showToast('La date de publication est requise', 'warning');
        if (!coverFiles.length) return U.showToast('Au moins une image de couverture est requise', 'warning');
        if (isPaid && price && parseFloat(price) <= 0) {
            return U.showToast('Le prix doit être supérieur à 0', 'warning');
        }

        saving = true;
        U.showLoader('Publication en cours...');

        try {
            const coverPaths = [];
            for (const file of coverFiles) {
                if (typeof file === 'string') {
                    coverPaths.push(file);
                } else {
                    const uploaded = await uploadFile(file);
                    coverPaths.push(uploaded.url);
                }
            }

            const payload = {
                title,
                content,
                category,
                cover: coverPaths.join(','),
                caption: caption || null,
                videos: videos || null,
                published_at: publishDate,
                is_featured: isFeatured,
                is_paid: isPaid,
                price: price || null,
            };

            const isUpdate = currentArticleId !== null;
            const articleResult = isUpdate
                ? await U.api(`${cfg.apiBase}/articles/${currentArticleId}`, { method: 'PUT', body: JSON.stringify(payload) })
                : await U.api(`${cfg.apiBase}/articles`, { method: 'POST', body: JSON.stringify(payload) });

            if (!isUpdate) {
                currentArticleId = articleResult.article_id || articleResult.article?.id;
            }

            let existingBlockIds = [];
            if (isUpdate) {
                const existing = await U.api(`${cfg.apiBase}/articles/${currentArticleId}/blocks`);
                existingBlockIds = (existing.blocks || []).map((b) => Number(b.id));
            }

            const processedBlockIds = [];
            for (const block of blocks) {
                const blockPayload = {
                    title: block.title,
                    content: block.content,
                    cover: null,
                    caption: block.caption,
                    videos: block.videos,
                    post_type: 'mixte',
                };

                if (block.cover instanceof File) {
                    const uploaded = await uploadFile(block.cover);
                    blockPayload.cover = uploaded.url;
                } else if (typeof block.cover === 'string') {
                    blockPayload.cover = block.cover;
                }

                const isBlockUpdate = block.id && isUpdate;
                const blockResult = isBlockUpdate
                    ? await U.api(`${cfg.apiBase}/blocks/${block.id}`, { method: 'PUT', body: JSON.stringify(blockPayload) })
                    : await U.api(`${cfg.apiBase}/articles/${currentArticleId}/blocks`, { method: 'POST', body: JSON.stringify(blockPayload) });

                const blockId = isBlockUpdate ? Number(block.id) : Number(blockResult.block_id || blockResult.block?.id);
                if (blockId) processedBlockIds.push(blockId);
            }

            if (isUpdate) {
                const toDelete = existingBlockIds.filter((id) => !processedBlockIds.includes(id));
                for (const blockId of toDelete) {
                    await U.api(`${cfg.apiBase}/blocks/${blockId}`, { method: 'DELETE' });
                }
            }

            U.showToast(isUpdate ? 'Article mis à jour avec succès' : 'Article publié avec succès', 'success');

            setTimeout(() => {
                if (articleResult.requires_payment && !pub.isAdmin) {
                    window.location.href = `${pub.backUrl}?new_article=${currentArticleId}`;
                } else {
                    window.location.href = pub.backUrl;
                }
            }, 1200);
        } catch (error) {
            U.showToast(error.message || 'Erreur lors de la publication', 'error');
        } finally {
            saving = false;
            U.hideLoader();
        }
    }

    async function uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('type', 'image');

        const res = await fetch(`${cfg.apiBase}/upload`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': U.csrf,
            },
            body: formData,
        });

        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.success) {
            throw new Error(data.message || 'Erreur lors de l\'upload');
        }

        return data;
    }

    async function goBack() {
        const confirmed = await U.confirm('Quitter l\'éditeur ? Les modifications non enregistrées seront perdues.');
        if (confirmed) window.location.href = pub.backUrl;
    }

    function decodeHtml(html) {
        const txt = document.createElement('textarea');
        txt.innerHTML = html;
        return txt.value;
    }
})();
