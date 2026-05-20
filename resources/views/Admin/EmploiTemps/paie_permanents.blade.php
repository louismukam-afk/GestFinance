@extends('layouts.app')

@section('content')
<style>
    .paie-param-card {
        overflow-x: auto;
    }

    .paie-param-table {
        min-width: 820px;
        table-layout: fixed;
    }

    .paie-param-table th,
    .paie-param-table td {
        vertical-align: middle !important;
        white-space: nowrap;
    }

    .paie-param-table input[type="number"],
    .paie-param-table input[type="date"] {
        min-width: 95px;
        padding-left: 6px;
        padding-right: 6px;
    }

    .paie-param-table .w-minmax {
        width: 120px;
    }

    .paie-param-table .w-taux {
        width: 95px;
    }

    .paie-param-table .w-date {
        width: 135px;
    }

    .paie-param-table .w-small {
        width: 70px;
    }

    .paie-param-table .w-action {
        width: 165px;
    }

    .paie-action-form {
        display: inline-block;
        margin: 0 3px 0 0;
    }
</style>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">{{ $title }}</h3>
        <a href="{{ route('emploi_temps.index') }}" class="btn btn-secondary">Retour</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><strong>Generer les bulletins brouillon</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('paie_permanents.generer') }}">
                @csrf
                <div class="row">
                    <div class="col-md-2">
                        <label>Periode debut</label>
                        <input type="date" name="periode_debut" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label>Periode fin</label>
                        <input type="date" name="periode_fin" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label>Import biometrie</label>
                        <select name="id_biometrie_import" class="form-control">
                            <option value="">-- Selon periode --</option>
                            @foreach($imports as $import)
                                <option value="{{ $import->id }}">{{ $import->libelle }} ({{ optional($import->date_debut)->format('d/m/Y') }} - {{ optional($import->date_fin)->format('d/m/Y') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Personnel</label>
                        <select name="id_personnel" class="form-control">
                            <option value="">Tous les permanents</option>
                            @foreach($personnels as $personnel)
                                <option value="{{ $personnel->id }}">{{ $personnel->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-success w-100">Generer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>Exporter les bulletins de paie PDF deja generes</strong></div>
        <div class="card-body">
            <div class="alert alert-info">
                Cette exportation ne cree pas les bulletins. Elle imprime uniquement les bulletins deja generes sur la periode de paie indiquee.
            </div>
            <form method="GET" action="{{ route('paie_permanents.bulletins.pdf') }}">
                <div class="row">
                    <div class="col-md-2">
                        <label>Debut periode paie</label>
                        <input type="date" name="periode_debut" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label>Fin periode paie</label>
                        <input type="date" name="periode_fin" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label>Personnel</label>
                        <select name="id_personnel" class="form-control">
                            <option value="">Tous les permanents</option>
                            @foreach($personnels as $personnel)
                                <option value="{{ $personnel->id }}">{{ $personnel->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Statut</label>
                        <select name="statut" class="form-control">
                            <option value="">Tous</option>
                            <option value="brouillon">Brouillon</option>
                            <option value="valide">Valide</option>
                            <option value="paye">Paye</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-danger w-100">Exporter PDF</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4" id="etats-paie">
        <div class="card-header"><strong>Generer un etat de paie depuis les bulletins</strong></div>
        <div class="card-body">
            <div class="alert alert-warning">
                Les dates ci-dessous correspondent a la periode de paie des bulletins, par exemple du 01/04/2026 au 30/04/2026. Il faut d'abord avoir genere les bulletins brouillon pour cette meme periode. L'export PDF des bulletins n'est pas obligatoire avant l'etat de paie.
            </div>
            <form method="POST" action="{{ route('paie_permanents.etats.generer') }}">
                @csrf
                <div class="row">
                    <div class="col-md-2">
                        <label>Debut periode paie</label>
                        <input type="date" name="periode_debut" value="{{ old('periode_debut') }}" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label>Fin periode paie</label>
                        <input type="date" name="periode_fin" value="{{ old('periode_fin') }}" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label>Annee academique</label>
                        <select name="id_annee_academique" class="form-control">
                            <option value="">Toutes</option>
                            @foreach($annees as $annee)
                                <option value="{{ $annee->id }}" {{ old('id_annee_academique') == $annee->id ? 'selected' : '' }}>{{ $annee->nom ?? $annee->libelle ?? $annee->annee_academique ?? ('Annee '.$annee->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Entite</label>
                        <select name="id_entite" class="form-control">
                            <option value="">Toutes</option>
                            @foreach($entites as $entite)
                                <option value="{{ $entite->id }}" {{ old('id_entite') == $entite->id ? 'selected' : '' }}>{{ $entite->nom_entite }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Statut bulletin</label>
                        <select name="statut" class="form-control">
                            <option value="">Tous</option>
                            <option value="brouillon" {{ old('statut') === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                            <option value="valide" {{ old('statut') === 'valide' ? 'selected' : '' }}>Valide</option>
                            <option value="paye" {{ old('statut') === 'paye' ? 'selected' : '' }}>Paye</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-info w-100">Generer etat</button>
                    </div>
                    <div class="col-md-12 mt-2">
                        <label>Observations</label>
                        <input name="observations" value="{{ old('observations') }}" class="form-control" placeholder="Observation facultative pour cet etat">
                    </div>
                </div>
            </form>

            <hr>
            <h5>Derniers etats generes</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Periode</th>
                            <th>Annee</th>
                            <th>Entite</th>
                            <th>Date generation</th>
                            <th class="text-end">Employes</th>
                            <th class="text-end">Gains</th>
                            <th class="text-end">Retenues</th>
                            <th class="text-end">Penalites</th>
                            <th class="text-end">Sanctions</th>
                            <th class="text-end">Acomptes</th>
                            <th class="text-end">Net</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($etatsPaie as $etatPaie)
                            <tr>
                                <td>{{ $etatPaie->reference }}</td>
                                <td>{{ optional($etatPaie->periode_debut)->format('d/m/Y') }} - {{ optional($etatPaie->periode_fin)->format('d/m/Y') }}</td>
                                <td>{{ $etatPaie->annee_academique->nom ?? $etatPaie->annee_academique->libelle ?? $etatPaie->annee_academique->annee_academique ?? 'Toutes' }}</td>
                                <td>{{ $etatPaie->entite->nom_entite ?? 'Toutes' }}</td>
                                <td>{{ optional($etatPaie->date_generation)->format('d/m/Y H:i') }}</td>
                                <td class="text-end">{{ $etatPaie->nombre_employes }}</td>
                                <td class="text-end">{{ number_format($etatPaie->total_gains, 0, ',', ' ') }}</td>
                                <td class="text-end">{{ number_format($etatPaie->total_retenues, 0, ',', ' ') }}</td>
                                <td class="text-end">{{ number_format($etatPaie->total_penalites, 0, ',', ' ') }}</td>
                                <td class="text-end">{{ number_format($etatPaie->total_sanctions, 0, ',', ' ') }}</td>
                                <td class="text-end">{{ number_format($etatPaie->total_acomptes, 0, ',', ' ') }}</td>
                                <td class="text-end"><strong>{{ number_format($etatPaie->total_net_a_payer, 0, ',', ' ') }}</strong></td>
                                <td>
                                    <a href="{{ route('paie_permanents.etats.show', $etatPaie) }}" class="btn btn-xs btn-primary">Voir</a>
                                    <a href="{{ route('paie_permanents.etats.pdf', $etatPaie) }}" class="btn btn-xs btn-danger">PDF</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="13" class="text-center text-muted">Aucun etat de paie genere.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><strong>Nouvelle rubrique de paie</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('paie_permanents.rubriques.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6"><label>Libelle</label><input name="libelle" class="form-control" required></div>
                            <div class="col-md-3">
                                <label>Type</label>
                                <select name="type" class="form-control">
                                    <option value="gain">Gain</option>
                                    <option value="retenue">Retenue</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Calcul</label>
                                <select name="mode_calcul" class="form-control">
                                    <option value="fixe">Fixe</option>
                                    <option value="pourcentage">Pourcentage</option>
                                    <option value="kilometrage">Kilometrage</option>
                                    <option value="manuel">Manuel</option>
                                </select>
                            </div>
                            <div class="col-md-4 mt-2">
                                <label>Base</label>
                                <select name="base_calcul" class="form-control">
                                    <option value="">Aucune</option>
                                    <option value="salaire_base">Salaire base</option>
                                    <option value="brut">Brut</option>
                                    <option value="taxable">Taxable</option>
                                    <option value="cotisable">Cotisable</option>
                                    <option value="net">Net</option>
                                </select>
                            </div>
                            <div class="col-md-4 mt-2"><label>Valeur defaut</label><input type="number" step="0.0001" name="valeur_defaut" value="0" class="form-control"></div>
                            <div class="col-md-4 mt-2"><label>Plafond</label><input type="number" step="0.01" name="plafond" class="form-control"></div>
                            <div class="col-md-12 mt-2">
                                <label class="mr-3"><input type="checkbox" name="imposable" value="1" checked> Imposable</label>
                                <label class="mr-3"><input type="checkbox" name="cotisable" value="1" checked> Cotisable CNPS</label>
                                <label><input type="checkbox" name="actif" value="1" checked> Actif</label>
                            </div>
                            <div class="col-md-12 mt-3"><button class="btn btn-primary">Creer rubrique</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><strong>Affecter une rubrique a un permanent</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('paie_permanents.configs.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <label>Personnel</label>
                                <select name="id_personnel" class="form-control" required>
                                    <option value="">-- Choisir --</option>
                                    @foreach($personnels as $personnel)
                                        <option value="{{ $personnel->id }}">{{ $personnel->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Rubrique</label>
                                <select name="id_rubrique_paie" class="form-control" required>
                                    <option value="">-- Choisir --</option>
                                    @foreach($rubriques->where('systeme', false) as $rubrique)
                                        <option value="{{ $rubrique->id }}">{{ $rubrique->libelle }} ({{ $rubrique->mode_calcul }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mt-2"><label>Valeur</label><input type="number" step="0.0001" name="valeur" class="form-control" required></div>
                            <div class="col-md-3 mt-2"><label>Quantite/km</label><input type="number" step="0.0001" name="quantite" value="1" class="form-control"></div>
                            <div class="col-md-3 mt-2"><label>Date debut</label><input type="date" name="date_debut" class="form-control" required></div>
                            <div class="col-md-3 mt-2"><label>Date fin</label><input type="date" name="date_fin" class="form-control"></div>
                            <div class="col-md-12 mt-2">
                                <label><input type="checkbox" name="appliquer_ce_mois" value="1" checked> Appliquer aux prochains bulletins de la periode</label>
                            </div>
                            <div class="col-md-12 mt-2"><label>Observations</label><input name="observations" class="form-control"></div>
                            <div class="col-md-12 mt-3"><button class="btn btn-primary">Affecter</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header"><strong>Bareme IRPP</strong></div>
                <div class="card-body paie-param-card">
                    <form method="POST" action="{{ route('paie_permanents.baremes_irpp.store') }}" class="mb-3">
                        @csrf
                        <div class="row">
                            <div class="col-md-2"><label>Min</label><input type="number" step="0.01" name="montant_min" class="form-control" required></div>
                            <div class="col-md-2"><label>Max</label><input type="number" step="0.01" name="montant_max" class="form-control"></div>
                            <div class="col-md-2"><label>Taux %</label><input type="number" step="0.0001" name="taux" class="form-control" required></div>
                            <div class="col-md-2"><label>Debut</label><input type="date" name="date_debut" class="form-control" required></div>
                            <div class="col-md-2"><label>Fin</label><input type="date" name="date_fin" class="form-control"></div>
                            <div class="col-md-1"><label>Ordre</label><input type="number" name="ordre" class="form-control" value="0"></div>
                            <div class="col-md-1 d-flex align-items-end"><button class="btn btn-primary btn-sm">+</button></div>
                            <div class="col-md-12 mt-2"><label><input type="checkbox" name="actif" value="1" checked> Actif</label></div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered paie-param-table">
                            <thead>
                                <tr>
                                    <th class="w-minmax">Min</th>
                                    <th class="w-minmax">Max</th>
                                    <th class="w-taux">Taux</th>
                                    <th class="w-date">Debut</th>
                                    <th class="w-date">Fin</th>
                                    <th class="w-small">Ordre</th>
                                    <th class="w-small">Actif</th>
                                    <th class="w-action">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($baremesIrpp as $bareme)
                                    <tr>
                                        <td colspan="8" style="padding: 0;">
                                            <form method="POST" action="{{ route('paie_permanents.baremes_irpp.update', $bareme) }}" id="bareme-update-{{ $bareme->id }}"></form>
                                            <form method="POST" action="{{ route('paie_permanents.baremes_irpp.destroy', $bareme) }}" id="bareme-delete-{{ $bareme->id }}"></form>
                                            <input form="bareme-update-{{ $bareme->id }}" type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input form="bareme-update-{{ $bareme->id }}" type="hidden" name="_method" value="PUT">
                                            <input form="bareme-delete-{{ $bareme->id }}" type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input form="bareme-delete-{{ $bareme->id }}" type="hidden" name="_method" value="DELETE">
                                            <table class="table table-sm mb-0" style="table-layout: fixed; min-width: 820px;">
                                                <tr>
                                                    <td class="w-minmax">
                                                        <input form="bareme-update-{{ $bareme->id }}" type="number" step="0.01" name="montant_min" value="{{ number_format($bareme->montant_min, 2, '.', '') }}" class="form-control input-sm" required>
                                                    </td>
                                                    <td class="w-minmax"><input form="bareme-update-{{ $bareme->id }}" type="number" step="0.01" name="montant_max" value="{{ $bareme->montant_max !== null ? number_format($bareme->montant_max, 2, '.', '') : '' }}" class="form-control input-sm"></td>
                                                    <td class="w-taux"><input form="bareme-update-{{ $bareme->id }}" type="number" step="0.0001" name="taux" value="{{ number_format($bareme->taux, 4, '.', '') }}" class="form-control input-sm" required></td>
                                                    <td class="w-date"><input form="bareme-update-{{ $bareme->id }}" type="date" name="date_debut" value="{{ optional($bareme->date_debut)->format('Y-m-d') }}" class="form-control input-sm" required></td>
                                                    <td class="w-date"><input form="bareme-update-{{ $bareme->id }}" type="date" name="date_fin" value="{{ optional($bareme->date_fin)->format('Y-m-d') }}" class="form-control input-sm"></td>
                                                    <td class="w-small"><input form="bareme-update-{{ $bareme->id }}" type="number" name="ordre" value="{{ $bareme->ordre }}" class="form-control input-sm"></td>
                                                    <td class="w-small text-center"><input form="bareme-update-{{ $bareme->id }}" type="checkbox" name="actif" value="1" {{ $bareme->actif ? 'checked' : '' }}></td>
                                                    <td class="w-action">
                                                        <button form="bareme-update-{{ $bareme->id }}" class="btn btn-xs btn-primary">Modifier</button>
                                                        <button form="bareme-delete-{{ $bareme->id }}" class="btn btn-xs btn-danger" onclick="return confirm('Supprimer cette tranche IRPP ?')">Supprimer</button>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-muted text-center">Aucun bareme IRPP.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header"><strong>Centimes additionnels communaux</strong></div>
                <div class="card-body paie-param-card">
                    <form method="POST" action="{{ route('paie_permanents.parametres_cac.store') }}" class="mb-3">
                        @csrf
                        <div class="row">
                            <div class="col-md-3"><label>Taux %</label><input type="number" step="0.0001" name="taux" class="form-control" required></div>
                            <div class="col-md-3"><label>Debut</label><input type="date" name="date_debut" class="form-control" required></div>
                            <div class="col-md-3"><label>Fin</label><input type="date" name="date_fin" class="form-control"></div>
                            <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary btn-sm">Enregistrer</button></div>
                            <div class="col-md-12 mt-2"><label><input type="checkbox" name="actif" value="1" checked> Actif</label></div>
                        </div>
                    </form>
                    <table class="table table-sm table-bordered paie-param-table" style="min-width: 560px;">
                        <thead>
                            <tr>
                                <th class="w-taux">Taux</th>
                                <th class="w-date">Debut</th>
                                <th class="w-date">Fin</th>
                                <th class="w-small">Actif</th>
                                <th class="w-action">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($parametresCac as $cac)
                                <tr>
                                    <td colspan="5" style="padding: 0;">
                                        <form method="POST" action="{{ route('paie_permanents.parametres_cac.update', $cac) }}" id="cac-update-{{ $cac->id }}"></form>
                                        <form method="POST" action="{{ route('paie_permanents.parametres_cac.destroy', $cac) }}" id="cac-delete-{{ $cac->id }}"></form>
                                        <input form="cac-update-{{ $cac->id }}" type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input form="cac-update-{{ $cac->id }}" type="hidden" name="_method" value="PUT">
                                        <input form="cac-delete-{{ $cac->id }}" type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input form="cac-delete-{{ $cac->id }}" type="hidden" name="_method" value="DELETE">
                                        <table class="table table-sm mb-0" style="table-layout: fixed; min-width: 560px;">
                                            <tr>
                                                <td class="w-taux">
                                                    <input form="cac-update-{{ $cac->id }}" type="number" step="0.0001" name="taux" value="{{ number_format($cac->taux, 4, '.', '') }}" class="form-control input-sm" required>
                                                </td>
                                                <td class="w-date"><input form="cac-update-{{ $cac->id }}" type="date" name="date_debut" value="{{ optional($cac->date_debut)->format('Y-m-d') }}" class="form-control input-sm" required></td>
                                                <td class="w-date"><input form="cac-update-{{ $cac->id }}" type="date" name="date_fin" value="{{ optional($cac->date_fin)->format('Y-m-d') }}" class="form-control input-sm"></td>
                                                <td class="w-small text-center"><input form="cac-update-{{ $cac->id }}" type="checkbox" name="actif" value="1" {{ $cac->actif ? 'checked' : '' }}></td>
                                                <td class="w-action">
                                                    <button form="cac-update-{{ $cac->id }}" class="btn btn-xs btn-primary">Modifier</button>
                                                    <button form="cac-delete-{{ $cac->id }}" class="btn btn-xs btn-danger" onclick="return confirm('Supprimer ce taux CAC ?')">Supprimer</button>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted text-center">Aucun taux CAC.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header"><strong>PVID / CNPS salariale</strong></div>
                <div class="card-body paie-param-card">
                    <form method="POST" action="{{ route('paie_permanents.parametres_pvid.store') }}" class="mb-3">
                        @csrf
                        <div class="row">
                            <div class="col-md-3"><label>Taux %</label><input type="number" step="0.0001" name="taux" class="form-control" required></div>
                            <div class="col-md-3"><label>Plafond</label><input type="number" step="0.01" name="plafond" class="form-control"></div>
                            <div class="col-md-3"><label>Debut</label><input type="date" name="date_debut" class="form-control" required></div>
                            <div class="col-md-3"><label>Fin</label><input type="date" name="date_fin" class="form-control"></div>
                            <div class="col-md-12 mt-2"><label><input type="checkbox" name="actif" value="1" checked> Actif</label></div>
                            <div class="col-md-12 mt-2"><button class="btn btn-primary btn-sm">Enregistrer</button></div>
                        </div>
                    </form>
                    <table class="table table-sm table-bordered paie-param-table" style="min-width: 690px;">
                        <thead>
                            <tr>
                                <th class="w-taux">Taux</th>
                                <th class="w-minmax">Plafond</th>
                                <th class="w-date">Debut</th>
                                <th class="w-date">Fin</th>
                                <th class="w-small">Actif</th>
                                <th class="w-action">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($parametresPvid as $pvid)
                                <tr>
                                    <td colspan="6" style="padding: 0;">
                                        <form method="POST" action="{{ route('paie_permanents.parametres_pvid.update', $pvid) }}" id="pvid-update-{{ $pvid->id }}"></form>
                                        <form method="POST" action="{{ route('paie_permanents.parametres_pvid.destroy', $pvid) }}" id="pvid-delete-{{ $pvid->id }}"></form>
                                        <input form="pvid-update-{{ $pvid->id }}" type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input form="pvid-update-{{ $pvid->id }}" type="hidden" name="_method" value="PUT">
                                        <input form="pvid-delete-{{ $pvid->id }}" type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input form="pvid-delete-{{ $pvid->id }}" type="hidden" name="_method" value="DELETE">
                                        <table class="table table-sm mb-0" style="table-layout: fixed; min-width: 690px;">
                                            <tr>
                                                <td class="w-taux">
                                                    <input form="pvid-update-{{ $pvid->id }}" type="number" step="0.0001" name="taux" value="{{ number_format($pvid->taux, 4, '.', '') }}" class="form-control input-sm" required>
                                                </td>
                                                <td class="w-minmax"><input form="pvid-update-{{ $pvid->id }}" type="number" step="0.01" name="plafond" value="{{ $pvid->plafond !== null ? number_format($pvid->plafond, 2, '.', '') : '' }}" class="form-control input-sm"></td>
                                                <td class="w-date"><input form="pvid-update-{{ $pvid->id }}" type="date" name="date_debut" value="{{ optional($pvid->date_debut)->format('Y-m-d') }}" class="form-control input-sm" required></td>
                                                <td class="w-date"><input form="pvid-update-{{ $pvid->id }}" type="date" name="date_fin" value="{{ optional($pvid->date_fin)->format('Y-m-d') }}" class="form-control input-sm"></td>
                                                <td class="w-small text-center"><input form="pvid-update-{{ $pvid->id }}" type="checkbox" name="actif" value="1" {{ $pvid->actif ? 'checked' : '' }}></td>
                                                <td class="w-action">
                                                    <button form="pvid-update-{{ $pvid->id }}" class="btn btn-xs btn-primary">Modifier</button>
                                                    <button form="pvid-delete-{{ $pvid->id }}" class="btn btn-xs btn-danger" onclick="return confirm('Supprimer ce taux PVID ?')">Supprimer</button>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-muted text-center">Aucun taux PVID.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="acomptes-sanctions">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><strong>Acompte sur salaire</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('paie_permanents.acomptes.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-5">
                                <label>Personnel</label>
                                <select name="id_personnel" class="form-control" required>
                                    <option value="">-- Choisir --</option>
                                    @foreach($personnels as $personnel)
                                        <option value="{{ $personnel->id }}">{{ $personnel->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3"><label>Date</label><input type="date" name="date_acompte" class="form-control" required></div>
                            <div class="col-md-4"><label>Mois imputation</label><input type="month" name="periode_imputation" class="form-control" required></div>
                            <div class="col-md-4 mt-2"><label>Montant</label><input type="number" step="0.01" name="montant" class="form-control" required></div>
                            <div class="col-md-8 mt-2"><label>Motif</label><input name="motif" class="form-control"></div>
                            <div class="col-md-12 mt-3"><button class="btn btn-warning">Enregistrer acompte</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><strong>Sanction salariale</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('paie_permanents.sanctions.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-5">
                                <label>Personnel</label>
                                <select name="id_personnel" class="form-control" required>
                                    <option value="">-- Choisir --</option>
                                    @foreach($personnels as $personnel)
                                        <option value="{{ $personnel->id }}">{{ $personnel->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3"><label>Date sanction</label><input type="date" name="date_sanction" class="form-control" required></div>
                            <div class="col-md-4"><label>Mois application</label><input type="month" name="mois_application" class="form-control"></div>
                            <div class="col-md-4 mt-2"><label>Montant</label><input type="number" step="0.01" name="montant" class="form-control" required></div>
                            <div class="col-md-8 mt-2"><label>Motif</label><input name="motif" class="form-control" required></div>
                            <div class="col-md-6 mt-2"><label>Periode debut</label><input type="date" name="periode_debut_application" class="form-control"></div>
                            <div class="col-md-6 mt-2"><label>Periode fin</label><input type="date" name="periode_fin_application" class="form-control"></div>
                            <div class="col-md-12 mt-2"><label>Description</label><input name="description" class="form-control"></div>
                            <div class="col-md-12 mt-3"><button class="btn btn-danger">Enregistrer sanction</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" class="card p-3 mb-4">
        <div class="row">
            <div class="col-md-3">
                <label>Personnel</label>
                <select name="id_personnel" class="form-control">
                    <option value="">Tous</option>
                    @foreach($personnels as $personnel)
                        <option value="{{ $personnel->id }}" {{ request('id_personnel') == $personnel->id ? 'selected' : '' }}>{{ $personnel->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><label>Periode debut</label><input type="date" name="periode_debut" value="{{ request('periode_debut') }}" class="form-control"></div>
            <div class="col-md-3"><label>Periode fin</label><input type="date" name="periode_fin" value="{{ request('periode_fin') }}" class="form-control"></div>
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Filtrer</button></div>
        </div>
    </form>

    <div class="card mb-4" id="bulletins-paie">
        <div class="card-header"><strong>Bulletins de paie</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('paie_permanents.bulletins.valider_global') }}" class="mb-3">
                @csrf
                <div class="row">
                    <div class="col-md-3">
                        <label>Debut periode paie</label>
                        <input type="date" name="periode_debut" value="{{ old('periode_debut', request('periode_debut')) }}" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label>Fin periode paie</label>
                        <input type="date" name="periode_fin" value="{{ old('periode_fin', request('periode_fin')) }}" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label>Personnel</label>
                        <select name="id_personnel" class="form-control">
                            <option value="">Tous les permanents</option>
                            @foreach($personnels as $personnel)
                                <option value="{{ $personnel->id }}" {{ old('id_personnel', request('id_personnel')) == $personnel->id ? 'selected' : '' }}>{{ $personnel->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-success w-100" onclick="return confirm('Valider tous les bulletins brouillon de cette periode ?')">Valider globalement</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Personnel</th>
                        <th>Periode</th>
                        <th>Salaire theorique</th>
                        <th>Penalite bio.</th>
                        <th>Brut</th>
                        <th>Taxable</th>
                        <th>Cotisable</th>
                        <th>Primes / gains</th>
                        <th>Acomptes</th>
                        <th>Sanctions</th>
                        <th>Retenue globale</th>
                        <th>Net</th>
                        <th>Statut</th>
                        <th>Lignes</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($bulletins as $bulletin)
                    <tr>
                        <td>{{ $bulletin->personnel->nom ?? '-' }}</td>
                        <td>{{ optional($bulletin->periode_debut)->format('d/m/Y') }} - {{ optional($bulletin->periode_fin)->format('d/m/Y') }}</td>
                        <td>{{ number_format($bulletin->salaire_base, 0, ',', ' ') }}</td>
                        <td>{{ number_format($bulletin->penalite_biometrie, 0, ',', ' ') }}</td>
                        <td>{{ number_format($bulletin->brut_mensuel, 0, ',', ' ') }}</td>
                        <td>{{ number_format($bulletin->salaire_taxable, 0, ',', ' ') }}</td>
                        <td>{{ number_format($bulletin->salaire_cotisable, 0, ',', ' ') }}</td>
                        <td>
                            @php($gains = $bulletin->lignes->where('sens', 'plus')->where('code', '!=', 'salaire_base_consolide')->where('montant', '>', 0))
                            <details>
                                <summary>{{ number_format($gains->sum('montant'), 0, ',', ' ') }}</summary>
                                <table class="table table-sm table-bordered mt-2">
                                    @forelse($gains as $gain)
                                        @php($gainTaux = $gain->taux > 0 ? ' ('.number_format($gain->taux, 2, ',', ' ').'%)' : '')
                                        <tr>
                                            <td>{{ $gain->libelle }}{{ $gainTaux }}</td>
                                            <td class="text-right">{{ number_format($gain->montant, 0, ',', ' ') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td class="text-muted">Aucune prime</td><td>0</td></tr>
                                    @endforelse
                                </table>
                            </details>
                        </td>
                        <td>{{ number_format($bulletin->total_acomptes, 0, ',', ' ') }}</td>
                        <td>{{ number_format($bulletin->total_sanctions, 0, ',', ' ') }}</td>
                        <td>
                            @php($retenues = $bulletin->lignes->where('sens', 'moins')->where('montant', '>', 0)->where('code', '!=', 'penalite_biometrie'))
                            @php($hasPenaliteLine = $bulletin->lignes->where('code', 'penalite_biometrie')->where('montant', '>', 0)->isNotEmpty())
                            @php($retenueGlobale = $hasPenaliteLine ? $bulletin->total_retenues : $bulletin->penalite_biometrie + $bulletin->total_retenues)
                            <strong>{{ number_format($retenueGlobale, 0, ',', ' ') }}</strong>
                            <details>
                                <summary>Detail</summary>
                                <table class="table table-sm table-bordered mt-2">
                                    @if($bulletin->penalite_biometrie > 0)
                                        <tr>
                                            <td>Penalite biometrie / avance</td>
                                            <td class="text-right">{{ number_format($bulletin->penalite_biometrie, 0, ',', ' ') }}</td>
                                        </tr>
                                    @endif
                                    @forelse($retenues as $retenue)
                                        @php($retenueTauxValeur = $retenue->taux > 0 ? $retenue->taux : (($retenue->base > 0 && !in_array($retenue->code, ['acomptes', 'sanctions'], true)) ? ($retenue->montant / $retenue->base) * 100 : 0))
                                        @php($retenueTaux = $retenueTauxValeur > 0 ? ' ('.number_format($retenueTauxValeur, 2, ',', ' ').'%)' : '')
                                        <tr>
                                            <td>{{ $retenue->libelle }}{{ $retenueTaux }}</td>
                                            <td class="text-right">{{ number_format($retenue->montant, 0, ',', ' ') }}</td>
                                        </tr>
                                    @empty
                                        @if($bulletin->penalite_biometrie <= 0)
                                            <tr><td class="text-muted">Aucune retenue</td><td>0</td></tr>
                                        @endif
                                    @endforelse
                                </table>
                            </details>
                        </td>
                        <td><strong>{{ number_format($bulletin->net_a_payer, 0, ',', ' ') }}</strong></td>
                        <td><span class="badge badge-{{ $bulletin->statut === 'valide' ? 'success' : 'warning' }}">{{ $bulletin->statut }}</span></td>
                        <td>
                            <details>
                                <summary>{{ $bulletin->lignes->count() }} ligne(s)</summary>
                                <table class="table table-sm table-bordered mt-2">
                                    @foreach($bulletin->lignes as $ligne)
                                        <tr>
                                            <td>{{ $ligne->libelle }}</td>
                                            <td>{{ $ligne->sens === 'plus' ? '+' : '-' }}</td>
                                            <td class="text-right">{{ number_format($ligne->montant, 0, ',', ' ') }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </details>
                        </td>
                        <td>
                            @if($bulletin->statut === 'brouillon')
                                <form method="POST" action="{{ route('paie_permanents.bulletins.valider', $bulletin) }}">
                                    @csrf
                                    <button class="btn btn-xs btn-success" onclick="return confirm('Valider ce bulletin ? Les acomptes et sanctions seront affectes a ce bulletin.')">Valider</button>
                                </form>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="15" class="text-center text-muted">Aucun bulletin genere.</td></tr>
                @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>Dernieres configurations, acomptes et sanctions</strong></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <h5>Rubriques personnel</h5>
                    <ul class="list-group">
                        @forelse($configs as $config)
                            <li class="list-group-item">{{ $config->personnel->nom ?? '-' }} - {{ $config->rubrique->libelle ?? '-' }} : {{ number_format($config->valeur, 2, ',', ' ') }}</li>
                        @empty
                            <li class="list-group-item text-muted">Aucune configuration.</li>
                        @endforelse
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Acomptes</h5>
                    <ul class="list-group">
                        @forelse($acomptes as $acompte)
                            <li class="list-group-item">{{ $acompte->personnel->nom ?? '-' }} - {{ $acompte->periode_imputation }} : {{ number_format($acompte->montant, 0, ',', ' ') }}</li>
                        @empty
                            <li class="list-group-item text-muted">Aucun acompte.</li>
                        @endforelse
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Sanctions</h5>
                    <ul class="list-group">
                        @forelse($sanctions as $sanction)
                            <li class="list-group-item">{{ $sanction->personnel->nom ?? '-' }} - {{ $sanction->mois_application ?: optional($sanction->periode_debut_application)->format('d/m/Y') }} : {{ number_format($sanction->montant, 0, ',', ' ') }}</li>
                        @empty
                            <li class="list-group-item text-muted">Aucune sanction.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('breadcrumb')
<ol class="breadcrumb" style="background-color: transparent;padding: 4px 10px">
    <li><a href="{{ route('home') }}"><strong>Accueil</strong></a></li>
    <li><a href="{{ route('emploi_temps.index') }}"><strong>Ressources humaines</strong></a></li>
    <li class="active"><strong>{{ $title }}</strong></li>
</ol>
@endsection
