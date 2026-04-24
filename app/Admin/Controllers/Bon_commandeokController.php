<?php

namespace App\Admin\Controllers;

use App\Models\bon_commandeok;
use App\Models\entite;
use App\Models\personnel;
use Encore\Admin\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Illuminate\Validation\Rule;

class Bon_commandeokController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'LISTE DES BONS DE COMMANDES';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new bon_commandeok());
        $grid->model()->with('personnels');
        $grid->column('id', __('Id'));
        $grid->column('nom_bon_commande', __('Nom bon commande'));
        $grid->column('description_bon_commande', __('Description bon commande'));
        $grid->column('date_debut', __('Date debut'));
        $grid->column('date_fin', __('Date fin'));
        $grid->column('date_entree_signature', __('Date entree signature'));
        //$grid->column('date_validation', __('Date validation'));

        $grid->column('date_validation', __('Date validation'))->display(function ($date) {
            if ($this->statuts == 1 && $date) {
                return "<span style='color:green; font-weight:bold;'>✔ $date</span>";
            } else {
                return "<span style='color:orange;'>⏳ En attente</span>";
            }
        })->sortable();
        $grid->column('montant_total', __('Montant total'));
        $grid->column('montant_realise', __('Montant realise'));
        $grid->column('reste', __('Reste'));
        $grid->column('montant_lettre', __('Montant lettre'));
        $grid->column('personnels.nom', __('personnel'));
        $grid->column('users.name', __('Utilisateur')); // Affiche le nom de l'utilisateur

        // Filtre
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->equal('id_personnel', __('Personnels'))
                ->select(personnel::orderBy('nom')->pluck('nom', 'id'));
        });
//        $grid->column('id_user', __('Id user'));
        $grid->column('statuts', 'Statut')->display(function ($statuts) {
            return $statuts == 1 ? '<span class="label label-success">Validé</span>' : '<span class="label label-warning">En attente</span>';
        });
//        $grid->column('statuts', __('Statuts'));
        $grid->column('entites.nom_entite', __('Entite'));
        /*$grid->column('validation_pdg', 'PDG');
        $grid->column('validation_daf', 'DAF');
        $grid->column('validation_achats', 'Achats');
        $grid->column('validation_emetteur', 'Emetteur');*/
        // ✅ Intégration de la colonne déroulante
        $grid->expandable('Éléments', function ($model) {
            $elements = $model->elements;

            if ($elements->isEmpty()) {
                return '<i>Aucun élément enregistré pour ce bon de commande.</i>';
            }

            $rows = '';
            foreach ($elements as $el) {
                $rows .= "<tr>
                <td>{$el->designation}</td>
                <td>{$el->quantite}</td>
                <td>{$el->prix_unitaire}</td>
                <td>{$el->total}</td>
            </tr>";
            }

            return <<<HTML
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Désignation</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
            </table>
            <a href="{$this->adminUrl("element_bon_commandes/create?bon_commande_id={$model->id}")}" 
                class="btn btn-xs btn-success" style="margin-top:10px;">
                ➕ Ajouter un élément
            </a>
          HTML;
        });
   


        $grid->column('actions_custom', __('Actions'))->display(function () {
            $id = $this->id;
            $html = '';

            if ($this->validation_pdg == 0) {
                $html .= '<a href="'.admin_url("valider-pdg/{$id}").'" class="btn btn-xs btn-success" style="margin:2px;">Valider PDG</a>';
            }

            if ($this->validation_daf == 0) {
                $html .= '<a href="'.admin_url("valider-daf/{$id}").'" class="btn btn-xs btn-info" style="margin:2px;">Valider DAF</a>';
            }

            if ($this->validation_achats == 0) {
                $html .= '<a href="'.admin_url("valider-achats/{$id}").'" class="btn btn-xs btn-warning" style="margin:2px;">Valider Achats</a>';
            }

            if ($this->validation_emetteur == 0) {
                $html .= '<a href="'.admin_url("valider-emetteur/{$id}").'" class="btn btn-xs btn-primary" style="margin:2px;">Valider Émetteur</a>';
            }
            // ✅ Nouveau bouton pour ajouter les éléments du bon de commande
            $html .= '<a href="'.admin_url("element_bon_commandes/create?id_bon_commande={$id}").'" class="btn btn-xs btn-default" style="margin:2px; background-color:#eee;">➕ Ajouter éléments</a>';

            return $html ?: '<span class="label label-default">Aucune action</span>';
        });
            $grid->column('created_at', __('Created at'));
            $grid->column('updated_at', __('Updated at'));

        return $grid;
           // Boutons d'action personnalisés
      /*  $grid->actions(function ($actions) {
            dd($actions->row);
            $id = $actions->getKey();
            $model = $actions->row;

             Test simple : affichage inconditionnel
             Exemple avec conditions
            /*$actions->append('<a href="'.admin_url("valider-pdg/{$id}").'" class="btn btn-xs btn-success">Test PDG</a>');
            $actions->append('<a href="'.admin_url("valider-daf/{$id}").'" class="btn btn-xs btn-info">Test DAF</a>');
            $actions->append('<a href="'.admin_url("valider-achats/{$id}").'" class="btn btn-xs btn-warning">Test Achats</a>');
            $actions->append('<a href="'.admin_url("valider-emetteur/{$id}").'" class="btn btn-xs btn-primary">Test Émetteur</a>');
            if ($model->validation_pdg == 0) {
                $actions->append('<a href="'.admin_url("valider-pdg/{$id}").'" class="btn btn-xs btn-success" style="margin-right:5px;">Valider PDG</a>');
            }

            if ($model->validation_daf == 0) {
                $actions->append('<a href="'.admin_url("valider-daf/{$id}").'" class="btn btn-xs btn-info" style="margin-right:5px;">Valider DAF</a>');
            }

            if ($model->validation_achats == 0) {
                $actions->append('<a href="'.admin_url("valider-achats/{$id}").'" class="btn btn-xs btn-warning" style="margin-right:5px;">Valider Achats</a>');
            }

            if ($model->validation_emetteur == 0) {
                $actions->append('<a href="'.admin_url("valider-emetteur/{$id}").'" class="btn btn-xs btn-primary" style="margin-right:5px;">Valider Émetteur</a>');
            }
        });
        $grid->actions(function ($actions) {
            $id = $actions->getKey();
            $model = $actions->row;

            if ($model->validation_pdg == 0) {
                $actions->append('<a href="'.admin_url("valider-pdg/{$id}").'" class="btn btn-sm btn-success" style="margin-right:5px;">Valider PDG</a>');
            }

            if ($model->validation_daf == 0) {
                $actions->append('<a href="'.admin_url("valider-daf/{$id}").'" class="btn btn-sm btn-info" style="margin-right:5px;">Valider DAF</a>');
            }

            if ($model->validation_achats == 0) {
                $actions->append('<a href="'.admin_url("valider-achats/{$id}").'" class="btn btn-sm btn-warning" style="margin-right:5px;">Valider Achats</a>');
            }

            if ($model->validation_emetteur == 0) {
                $actions->append('<a href="'.admin_url("valider-emetteur/{$id}").'" class="btn btn-sm btn-primary" style="margin-right:5px;">Valider Émetteur</a>');
            }
        });*/
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(bon_commandeok::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('nom_bon_commande', __('Nom bon commande'));
        $show->field('description_bon_commande', __('Description bon commande'));
        $show->field('date_debut', __('Date debut'));
        $show->field('date_fin', __('Date fin'));
        $show->field('date_entree_signature', __('Date entree signature'));
        $show->field('date_validation', __('Date validation'));
        $show->field('montant_total', __('Montant total'));
        $show->field('montant_realise', __('Montant realise'));
        $show->field('reste', __('Reste'));
        $show->field('montant_lettre', __('Montant lettre'));
        $show->field('id_personnel', __('Id personnel'));
        $show->field('id_user', __('Id user'));
        $show->field('statuts', __('Statuts'));
        $show->field('id_entite', __('Id entite'));
        $show->field('validation_pdg', __('Validation pdg'));
        $show->field('validation_daf', __('Validation daf'));
        $show->field('validation_achats', __('Validation achats'));
        $show->field('validation_emetteur', __('Validation emetteur'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new bon_commandeok());

        $form->text('nom_bon_commande', __('Nom bon commande'))
            ->rules(function ($form) {
                return [
                    'required',
                    Rule::unique('bon_commandeoks', 'nom_bon_commande')->ignore($form->model()->id)
                ];
            });;
        $form->text('description_bon_commande', __('Description bon commande'));
        $form->date('date_debut', __('Date debut'))->default(date('Y-m-d'));
        $form->date('date_fin', __('Date fin'))->default(date('Y-m-d'));
        $form->date('date_entree_signature', __('Date entree signature'))->default(date('Y-m-d'));
        $form->date('date_validation', __('Date validation'))->readonly();
        $form->decimal('montant_total', __('Montant total'))->rules('required|numeric|min:0');
        $form->decimal('montant_realise', __('Montant realise'))->rules('required|numeric|min:0');
       // $form->decimal('montant_total', __('Montant total'));
      //  $form->decimal('montant_realise', __('Montant realise'));
        $form->decimal('reste', __('Reste'))->default(0)->readonly(); // Affichage simple

        $form->text('montant_lettre', __('Montant lettre'));
        $form->select('id_personnel', __('Personnels'))
            ->options(personnel::orderBy('nom')->pluck('nom', 'id'))
            ->required();



       /* $form->saving(function (Form $form) {
            $form->id_user = \Admin::user()->id; // ou Auth::id() si hors de Laravel Admin
            dump($form->id_user = \Admin::user()->id);
            die();
        });*/

//        $form->number('id_user', __('Id user'));
//        $form->number('statuts', __('Statuts'));
        $form->hidden('statuts')->default(0);
        $form->hidden('validation_pdg')->default(0);
        $form->hidden('validation_daf')->default(0);
        $form->hidden('validation_achats')->default(0);
        $form->hidden('validation_emetteur')->default(0);
        $form->select('id_entite', __('entites'))
            ->options(entite::orderBy('nom_entite')->pluck('nom_entite', 'id'))
            ->required();
       // $form->number('id_entite', __('Id entite'));
        $form->saving(function (Form $form) {
            $form->model()->id_user = \Admin::user()->id;
           // Calcule automatique du champ reste
        $form->model()->reste = $form->model()->montant_total - $form->model()->montant_realise;

            $form->model()->statuts = 0;
            $form->model()->validation_pdg = 0;
            $form->model()->validation_daf = 0;
            $form->model()->validation_achats = 0;
            $form->model()->validation_emetteur = 0;
        });
       /* $form->saving(function (Form $form) {
            $form->statuts = 0;
            $form->validation_pdg = 0;
            $form->validation_daf = 0;
            $form->validation_achats = 0;
            $form->validation_emetteur = 0;
        });*/

        $form->html("
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                function updateReste() {
                    var total = parseFloat(document.querySelector('input[name=\"montant_total\"]').value) || 0;
                    var realise = parseFloat(document.querySelector('input[name=\"montant_realise\"]').value) || 0;
                    var reste = total - realise;
                    document.querySelector('input[name=\"reste\"]').value = reste.toFixed(2);
                }

                document.querySelector('input[name=\"montant_total\"]').addEventListener('input', updateReste);
                document.querySelector('input[name=\"montant_realise\"]').addEventListener('input', updateReste);
            });
        </script>
    ");

        return $form;
    }
    public function validerPDG($id)
    {
        $commande = bon_commandeok::findOrFail($id);
        $commande->validation_pdg = 1;
        $this->updateStatut($commande);
        admin_toastr('Validation PDG réussie', 'success');
        return redirect(admin_url('bon_commandeoks'));
    }

    public function validerDAF($id)
    {
        $commande = bon_commandeok::findOrFail($id);
        $commande->validation_daf = 1;
        $this->updateStatut($commande);
        admin_toastr('Validation DAF réussie', 'success');
        return redirect(admin_url('bon_commandeoks'));
    }

    public function validerAchats($id)
    {
        $commande = bon_commandeok::findOrFail($id);
        $commande->validation_achats = 1;
        $this->updateStatut($commande);
        admin_toastr('Validation Achats réussie', 'success');
        return redirect(admin_url('bon_commandeoks'));
    }

    public function validerEmetteur($id)
    {
        $commande = bon_commandeok::findOrFail($id);
        $commande->validation_emetteur = 1;
        $this->updateStatut($commande);
        admin_toastr('Validation Émetteur réussie', 'success');
        return redirect(admin_url('bon_commandeoks'));
    }

    private function updateStatut($commande)
    {
        // Si toutes les validations sont faites, on valide le bon de commande
        if (
            $commande->validation_pdg &&
            $commande->validation_daf &&
            $commande->validation_achats &&
            $commande->validation_emetteur
        ) {
            $commande->statuts = 1; // validé
            $commande->date_validation = now(); // Enregistre la date actuelle
        }

        $commande->save();
    }


}

