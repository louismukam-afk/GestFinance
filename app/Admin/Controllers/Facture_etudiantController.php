<?php

namespace App\Admin\Controllers;

use App\Models\facture_etudiant;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class Facture_etudiantController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'facture_etudiant';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new facture_etudiant());

        $grid->column('id', __('Id'));
        $grid->column('id_cycle', __('Id cycle'));
        $grid->column('id_niveau', __('Id niveau'));
        $grid->column('id_filiere', __('Id filiere'));
        $grid->column('id_scolarite', __('Id scolarite'));
        $grid->column('id_frais', __('Id frais'));
        $grid->column('id_tranche_scolarite', __('Id tranche scolarite'));
        $grid->column('id_specialite', __('Id specialite'));
        $grid->column('id_etudiant', __('Id etudiant'));
        $grid->column('id_budget', __('Id budget'));
        $grid->column('id_ligne_budgetaire_entree', __('Id ligne budgetaire entree'));
        $grid->column('id_element_ligne_budgetaire_entree', __('Id element ligne budgetaire entree'));
        $grid->column('id_donnee_ligne_budgetaire_entree', __('Id donnee ligne budgetaire entree'));
        $grid->column('montant_total_facture', __('Montant total facture'));
        $grid->column('numero_facture', __('Numero facture'));
        $grid->column('date_facture', __('Date facture'));
        $grid->column('id_annee_academique', __('Id annee academique'));
        $grid->column('type_facture', __('Type facture'));
        $grid->column('id_user', __('Id user'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(facture_etudiant::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('id_cycle', __('Id cycle'));
        $show->field('id_niveau', __('Id niveau'));
        $show->field('id_filiere', __('Id filiere'));
        $show->field('id_scolarite', __('Id scolarite'));
        $show->field('id_frais', __('Id frais'));
        $show->field('id_tranche_scolarite', __('Id tranche scolarite'));
        $show->field('id_specialite', __('Id specialite'));
        $show->field('id_etudiant', __('Id etudiant'));
        $show->field('id_budget', __('Id budget'));
        $show->field('id_ligne_budgetaire_entree', __('Id ligne budgetaire entree'));
        $show->field('id_element_ligne_budgetaire_entree', __('Id element ligne budgetaire entree'));
        $show->field('id_donnee_ligne_budgetaire_entree', __('Id donnee ligne budgetaire entree'));
        $show->field('montant_total_facture', __('Montant total facture'));
        $show->field('numero_facture', __('Numero facture'));
        $show->field('date_facture', __('Date facture'));
        $show->field('id_annee_academique', __('Id annee academique'));
        $show->field('type_facture', __('Type facture'));
        $show->field('id_user', __('Id user'));
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
        $form = new Form(new facture_etudiant());

        $form->number('id_cycle', __('Id cycle'));
        $form->number('id_niveau', __('Id niveau'));
        $form->number('id_filiere', __('Id filiere'));
        $form->number('id_scolarite', __('Id scolarite'));
        $form->number('id_frais', __('Id frais'));
        $form->number('id_tranche_scolarite', __('Id tranche scolarite'));
        $form->number('id_specialite', __('Id specialite'));
        $form->number('id_etudiant', __('Id etudiant'));
        $form->number('id_budget', __('Id budget'));
        $form->number('id_ligne_budgetaire_entree', __('Id ligne budgetaire entree'));
        $form->number('id_element_ligne_budgetaire_entree', __('Id element ligne budgetaire entree'));
        $form->number('id_donnee_ligne_budgetaire_entree', __('Id donnee ligne budgetaire entree'));
        $form->decimal('montant_total_facture', __('Montant total facture'));
        $form->number('numero_facture', __('Numero facture'));
        $form->date('date_facture', __('Date facture'))->default(date('Y-m-d'));
        $form->number('id_annee_academique', __('Id annee academique'));
        $form->number('type_facture', __('Type facture'));
        $form->number('id_user', __('Id user'));

        return $form;
    }
}
