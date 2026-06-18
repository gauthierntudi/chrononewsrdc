@extends('layouts.dashboard')

@section('title', ($articleId ? 'Modifier' : 'Publier').' — '.config('chrononews.name'))
@section('body-class', 'publisher-page')

@section('sidebar')
    @if($isAdmin)
        @include('dashboard.partials.sidebar-admin')
    @else
        @include('dashboard.partials.sidebar-user')
    @endif
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
<link rel="stylesheet" href="{{ asset('css/dashboard/publisher.css') }}">
@endpush

@section('content')
<div class="publisher-container">
    <div class="publisher-header">
        <h1>
            <i data-lucide="pen-line" class="lucide-icon" aria-hidden="true"></i>
            <span id="pageTitle">{{ $articleId ? 'Modifier l\'article' : 'Nouvel article' }}</span>
        </h1>
        <div class="publisher-actions">
            <button type="button" class="btn btn-secondary" id="publisherBackBtn">
                <i data-lucide="arrow-left" class="lucide-icon" aria-hidden="true"></i>
                Retour
            </button>
            <button type="button" class="btn btn-primary" id="publisherSaveBtn">
                <i data-lucide="check" class="lucide-icon" aria-hidden="true"></i>
                Publier
            </button>
        </div>
    </div>

    <div class="article-metadata">
        <h2>Informations de l'article</h2>
        <div class="form-grid">
            <div class="publisher-form-group form-grid-full">
                <label for="articleTitle">Titre de l'article *</label>
                <input type="text" id="articleTitle" placeholder="Entrez le titre de votre article" required>
            </div>

            <div class="publisher-form-group form-grid-full">
                <label for="articleDescription">Description *</label>
                <textarea id="articleDescription" placeholder="Résumé de votre article (visible dans les listes)" required></textarea>
            </div>

            <div class="publisher-form-group">
                <label for="articleCategory">Catégorie *</label>
                <select id="articleCategory" required>
                    <option value="">Sélectionner une catégorie</option>
                    @foreach(config('chrononews.article.categories', []) as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
            </div>

            <div class="publisher-form-group">
                <label for="articlePublishDate">Date de publication *</label>
                <div class="publisher-date-field">
                    <input type="text" id="articlePublishDate" class="publisher-date-input" data-input placeholder="Sélectionner la date et l'heure" required readonly>
                    <i data-lucide="calendar" class="lucide-icon publisher-date-icon" aria-hidden="true"></i>
                </div>
            </div>

            <div class="publisher-form-group form-grid-full">
                <label>Images de couverture *</label>
                <div class="upload-zone" id="coverUploadZone">
                    <div id="coverPlaceholder">
                        <i data-lucide="cloud-upload" class="lucide-icon" style="width:2.5rem;height:2.5rem;color:#9ca3af;" aria-hidden="true"></i>
                        <p style="margin:0.5rem 0 0;color:#6b7280;font-size:0.875rem;">Cliquez ou glissez des images (plusieurs possible)</p>
                    </div>
                    <div id="coverPreview" class="cover-previews" style="display:none;"></div>
                </div>
                <input type="file" id="coverInput" accept="image/*" multiple hidden>
            </div>

            <div class="publisher-form-group form-grid-full">
                <label for="coverCaption">Légende des images de couverture</label>
                <input type="text" id="coverCaption" placeholder="Description des images (optionnel)">
            </div>

            <div class="publisher-form-group form-grid-full">
                <label for="articleVideo">Lien vidéo YouTube <span class="muted">(facultatif)</span></label>
                <input type="url" id="articleVideo" placeholder="URL de la vidéo YouTube">
            </div>

            <div class="publisher-form-group form-grid-full publisher-options">
                <h3>Options de publication</h3>
                <div class="option-cards">
                    <label class="option-card">
                        <div>
                            <strong>À la une</strong>
                            <span class="muted" style="display:block;font-size:0.75rem;">Mettre en avant sur l'accueil</span>
                        </div>
                        <div class="switch">
                            <input type="checkbox" id="articleFeatured">
                            <span class="slider"></span>
                        </div>
                    </label>
                    <label class="option-card">
                        <div>
                            <strong>Article payant</strong>
                            <span class="muted" style="display:block;font-size:0.75rem;">Accès restreint aux acheteurs</span>
                        </div>
                        <div class="switch">
                            <input type="checkbox" id="articleIsPaid">
                            <span class="slider"></span>
                        </div>
                    </label>
                </div>
                <div class="publisher-form-group" id="priceFieldContainer" style="display:none;margin-top:1rem;">
                    <label for="articlePrice">Prix de l'article (USD)</label>
                    <input type="number" id="articlePrice" min="0.5" step="0.1" placeholder="Laisser vide pour le prix par défaut">
                </div>
            </div>
        </div>
    </div>

    <div class="blocks-section">
        <div class="blocks-header">
            <h2>Blocs de contenu</h2>
            <button type="button" class="btn btn-primary" id="addBlockBtn">
                <i data-lucide="plus" class="lucide-icon" aria-hidden="true"></i>
                Ajouter un bloc
            </button>
        </div>

        <div id="blocksList" class="blocks-list">
            <div class="empty-state">
                <i data-lucide="box" class="lucide-icon" aria-hidden="true"></i>
                <p>Aucun bloc ajouté. Cliquez sur « Ajouter un bloc » pour commencer.</p>
            </div>
        </div>

        <div id="blockEditor" class="block-editor" style="display:none;">
            <h3 id="editorTitle">Nouveau bloc</h3>
            <p class="muted" style="font-size:0.875rem;margin-bottom:1rem;">Remplissez au moins un des champs ci-dessous</p>

            <div class="publisher-form-group" style="margin-bottom:1rem;">
                <label for="blockTitle">Titre du bloc (optionnel)</label>
                <input type="text" id="blockTitle" placeholder="Titre de section">
            </div>

            <div style="margin-bottom:1rem;">
                <label style="font-weight:600;font-size:0.875rem;display:block;margin-bottom:0.5rem;">Contenu texte (optionnel)</label>
                <div id="blockContentEditor"></div>
            </div>

            <div class="publisher-form-group" style="margin-bottom:1rem;">
                <label>Image du bloc (optionnel)</label>
                <div class="upload-zone" id="blockImageUploadZone">
                    <div id="blockImagePlaceholder">
                        <i data-lucide="image" class="lucide-icon" style="width:2.5rem;height:2.5rem;color:#9ca3af;" aria-hidden="true"></i>
                        <p style="margin:0.5rem 0 0;color:#6b7280;font-size:0.875rem;">Cliquez pour ajouter une image</p>
                    </div>
                    <div id="blockImagePreview" style="display:none;"></div>
                </div>
                <input type="file" id="blockImageInput" accept="image/*" hidden>
            </div>

            <div class="publisher-form-group" style="margin-bottom:1rem;">
                <label for="blockImageCaption">Légende de l'image (optionnel)</label>
                <input type="text" id="blockImageCaption" placeholder="Description de l'image">
            </div>

            <div class="publisher-form-group" style="margin-bottom:1rem;">
                <label for="blockVideoUrl">URL de la vidéo YouTube (optionnel)</label>
                <input type="url" id="blockVideoUrl" placeholder="https://youtube.com/watch?v=...">
            </div>

            <div class="editor-actions">
                <button type="button" class="btn btn-secondary" id="cancelBlockBtn">Annuler</button>
                <button type="button" class="btn btn-primary" id="saveBlockBtn">
                    <span id="saveBlockText">Ajouter le bloc</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.CHRONONEWS_PUBLISHER = {
        articleId: @json($articleId ? (int) $articleId : null),
        isAdmin: @json($isAdmin),
        backUrl: @json($backUrl),
    };
</script>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/fr.js"></script>
<script src="{{ asset('js/dashboard/ai-text-corrector.js') }}"></script>
<script src="{{ asset('js/dashboard/publisher.js') }}"></script>
@endpush
