@php
    $entite = $bon->entites;
    $logoPath = $entite->logo ?? 'uploads/images/1759420569_logo.jpg';
    $logoFullPath = public_path($logoPath);
    $logoSrc = null;

    if ($logoPath && file_exists($logoFullPath)) {
        $mime = mime_content_type($logoFullPath) ?: 'image/png';
        $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoFullPath));
    }

    $elementsTotal = $elements->sum('montant_total_element_bon_commande');
    $reste = $bon->montant_total - $elementsTotal;

    $validationSteps = [
        'Emetteur' => $bon->validationState('emetteur'),
        'Achats' => $bon->validationState('achats'),
        'DAF' => $bon->validationState('daf'),
        'PDG' => $bon->validationState('pdg'),
    ];
@endphp

<div class="bon-document">
    <div class="bon-sheet">
        <table class="doc-header">
            <tr>
                <td class="logo-cell">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" alt="Logo">
                    @else
                        <div class="logo-fallback">GF</div>
                    @endif
                </td>
                <td class="entity-cell">
                    <h2>{{ $entite->nom_entite ?? config('app.name', 'GESFINANCE') }}</h2>
                    <div>{{ $entite->localisation ?? '' }}</div>
                    <div>
                        @if($entite && $entite->telephone) Tel : {{ $entite->telephone }} @endif
                        @if($entite && $entite->email) | Email : {{ $entite->email }} @endif
                    </div>
                </td>
                <td class="ref-cell">
                    <strong>BON DE COMMANDE</strong>
                    <span>N&deg; {{ str_pad($bon->id, 4, '0', STR_PAD_LEFT) }}</span>
                    <small>Date : {{ optional($bon->created_at)->format('d/m/Y') }}</small>
                </td>
            </tr>
        </table>

        <h1>Bon de commande</h1>

        <table class="info-grid">
            <tr>
                <td>
                    <span>Objet du bon</span>
                    <strong>{{ $bon->nom_bon_commande }}</strong>
                </td>
                <td>
                    <span>Entite</span>
                    <strong>{{ $entite->nom_entite ?? '-' }}</strong>
                </td>
            </tr>
            <tr>
                <td>
                    <span>Demandeur / Personnel</span>
                    <strong>{{ $bon->personnels->nom ?? '-' }}</strong>
                </td>
                <td>
                    <span>Emetteur</span>
                    <strong>{{ $bon->user->name ?? '-' }}</strong>
                </td>
            </tr>
            <tr>
                <td>
                    <span>Periode</span>
                    <strong>Du {{ \Carbon\Carbon::parse($bon->date_debut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($bon->date_fin)->format('d/m/Y') }}</strong>
                </td>
                <td>
                    <span>Date entree signature</span>
                    <strong>{{ \Carbon\Carbon::parse($bon->date_entree_signature)->format('d/m/Y') }}</strong>
                </td>
            </tr>
        </table>

        <div class="description-block">
            <span>Description / Justification</span>
            <p>{{ $bon->description_bon_commande }}</p>
        </div>

        <table class="items-table">
            <thead>
            <tr>
                <th class="center">N&deg;</th>
                <th>Designation</th>
                <th>Description</th>
                <th class="center">Qte</th>
                <th class="right">Prix unitaire</th>
                <th class="right">Montant</th>
                <th class="center">Date realisation</th>
            </tr>
            </thead>
            <tbody>
            @forelse($elements as $index => $element)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $element->nom_element_bon_commande }}</td>
                    <td>{{ $element->description_elements_bon_commande ?: '-' }}</td>
                    <td class="center">{{ $element->quantite_element_bon_commande }}</td>
                    <td class="right">{{ number_format($element->prix_unitaire_element_bon_commande, 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($element->montant_total_element_bon_commande, 0, ',', ' ') }}</td>
                    <td class="center">{{ $element->date_realisation ? \Carbon\Carbon::parse($element->date_realisation)->format('d/m/Y') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center muted">Aucun element saisi pour ce bon.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <table class="totals-table">
            <tr>
                <td>Montant total des elements</td>
                <td>{{ number_format($elementsTotal, 0, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <td>Montant du bon</td>
                <td>{{ number_format($bon->montant_total, 0, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <td>Reste</td>
                <td>{{ number_format($reste, 0, ',', ' ') }} FCFA</td>
            </tr>
            <tr class="grand-total">
                <td>Montant en lettres</td>
                <td>{{ $bon->montant_lettre }}</td>
            </tr>
        </table>

        <table class="validation-table">
            <tr>
                @foreach($validationSteps as $label => $state)
                    <td>
                        <strong>{{ $label }}</strong>
                        <span class="state state-{{ $state }}">
                            {{ $state === 'valide' ? 'Valide' : ($state === 'refuse' ? 'Refuse' : 'En attente') }}
                        </span>
                        <div class="signature-line"></div>
                    </td>
                @endforeach
            </tr>
        </table>

        @if($bon->motif_refus)
            <div class="refusal-box">
                <strong>Motif du refus :</strong> {{ $bon->motif_refus }}
            </div>
        @endif
    </div>
</div>
