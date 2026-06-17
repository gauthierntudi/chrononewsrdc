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
        const input = document.getElementById('articlePublishDate');
        if (!input || typeof flatpickr === 'undefined') return;

        publishDatePicker = flatpickr(input, {
            enableTime: true,
            time_24hr: true,
            dateFormat: 'Y-m-d H:i',
            locale: 'fr',
            minuteIncrement: 1,
            allowInput: true,
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
        document.getElementById('addBlockBtn')?.addEventListener('click', showBlockEditor);
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

        const blockZone = document.getElementById('blockImageUploadZone');
        const blockInput = document.getElementById('blockImageInput');
        blockZone?.addEventListener('click', () => blockInput?.click());
        blockInput?.addEventListener('change', (e) => {
            const file = e.target.files?.[0];
            if (file) handleBlockImageSelect(file);
        });
    }

    function togglePriceField() {
        const container = document.getElementById('priceFieldContainer');
        const checked = document.getElementById('articleIsPaid')?.checked;
        if (container) container.style.display = checked ? 'block' : 'none';
    }

    function handleCoverSelect(files) {
        coverFiles = files;
        renderCoverPreviews();
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
            const wrap = document.createElement('div');
            wrap.className = 'cover-preview-item';

            if (typeof file === 'string') {
                wrap.innerHTML = `
                    <img src="${U.mediaUrl(file)}" alt="Couverture">
                    <button type="button" class="remove-cover-btn" data-index="${index}" aria-label="Supprimer">&times;</button>
                `;
                preview.appendChild(wrap);
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                wrap.innerHTML = `
                    <img src="${e.target.result}" alt="Couverture">
                    <button type="button" class="remove-cover-btn" data-index="${index}" aria-label="Supprimer">&times;</button>
                `;
                preview.appendChild(wrap);
            };
            reader.readAsDataURL(file);
        });

        preview.querySelectorAll('.remove-cover-btn').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const idx = Number(btn.dataset.index);
                coverFiles = coverFiles.filter((_, i) => i !== idx);
                renderCoverPreviews();
            });
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
        currentBlockIndex = index;
        document.getElementById('editorTitle').textContent = index !== null ? 'Modifier le bloc' : 'Nouveau bloc';
        document.getElementById('saveBlockText').textContent = index !== null ? 'Enregistrer le bloc' : 'Ajouter le bloc';
        resetBlockForm();

        if (index !== null && blocks[index]) {
            const block = blocks[index];
            document.getElementById('blockTitle').value = block.title || '';
            quillEditor?.clipboard.dangerouslyPasteHTML(block.content || '');
            document.getElementById('blockImageCaption').value = block.caption || '';
            document.getElementById('blockVideoUrl').value = block.videos || '';
            if (block.cover && typeof block.cover === 'string') {
                blockImageFile = block.cover;
                const preview = document.getElementById('blockImagePreview');
                const placeholder = document.getElementById('blockImagePlaceholder');
                document.getElementById('blockImageUploadZone')?.classList.add('has-image');
                if (placeholder) placeholder.style.display = 'none';
                if (preview) {
                    preview.style.display = 'block';
                    preview.innerHTML = `
                        <div class="cover-preview-item" style="width:200px;height:140px;">
                            <img src="${U.mediaUrl(block.cover)}" alt="Aperçu bloc">
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
        document.getElementById('blockEditor').scrollIntoView({ behavior: 'smooth' });
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
        const hasContent = textContent && textContent !== '';
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

        if (currentBlockIndex !== null) {
            if (blocks[currentBlockIndex]?.id) blockData.id = blocks[currentBlockIndex].id;
            if (!blockImageFile && blocks[currentBlockIndex]?.cover) {
                blockData.cover = blocks[currentBlockIndex].cover;
            }
            blocks[currentBlockIndex] = blockData;
        } else {
            blocks.push(blockData);
        }

        renderBlocks();
        cancelBlockEdit();
        U.showToast('Bloc enregistré localement', 'success');
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

        list.innerHTML = blocks.map((block, index) => `
            <div class="block-card">
                <div class="block-card-header">
                    <div>
                        <strong>${U.escapeHtml(block.title || `Bloc ${index + 1}`)}</strong>
                        ${block.content ? `<p class="muted" style="margin:0.35rem 0 0;font-size:0.875rem;">Contenu texte</p>` : ''}
                        ${block.videos ? `<p class="muted" style="margin:0.25rem 0 0;font-size:0.875rem;">Vidéo YouTube</p>` : ''}
                        ${block.cover ? `<p class="muted" style="margin:0.25rem 0 0;font-size:0.875rem;">Image jointe</p>` : ''}
                    </div>
                    <div class="block-card-actions">
                        <button type="button" class="btn btn-secondary btn-sm" data-edit-block="${index}">Modifier</button>
                        <button type="button" class="btn btn-danger btn-sm" data-delete-block="${index}">Supprimer</button>
                    </div>
                </div>
            </div>
        `).join('');

        list.querySelectorAll('[data-edit-block]').forEach((btn) => {
            btn.addEventListener('click', () => showBlockEditor(Number(btn.dataset.editBlock)));
        });
        list.querySelectorAll('[data-delete-block]').forEach((btn) => {
            btn.addEventListener('click', () => {
                blocks.splice(Number(btn.dataset.deleteBlock), 1);
                renderBlocks();
            });
        });
    }

    async function loadArticle(id) {
        const data = await U.api(`${cfg.apiBase}/articles/${id}`);
        const article = data.article;
        if (!article) throw new Error('Article non trouvé');

        currentArticleId = article.id;
        document.getElementById('pageTitle').textContent = 'Modifier l\'article';
        document.getElementById('articleTitle').value = decodeHtml(article.title || '');
        document.getElementById('articleDescription').value = article.content || '';
        document.getElementById('articleCategory').value = article.category || '';

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
        const publishDate = document.getElementById('articlePublishDate').value;
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
