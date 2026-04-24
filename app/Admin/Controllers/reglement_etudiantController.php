<?php

namespace App\Admin\Controllers;

use App\Models\reglement_etudiant;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class reglement_etudiantController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'reglement_etudiant';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new reglement_etudiant());

        $grid->column('id', __('Id'));
        $grid->column('id_cycle', __('Id cycle'));
        $grid->column('id_niveau', __('Id niveau'));
        $grid->column('id_caisse', __('Id caisse'));
        $grid->column('id_banque', __('Id banque'));
        $grid->column('id_filière', __('Id filière'));
        $grid->column('id_scolarité', __('Id scolarité'));
        $grid->column('id_frais', __('Id frais'));
        $grid->column('id_tranche_scolarite', __('Id tranche scolarite'));
        $grid->column('id_specialite', __('Id specialite'));
        $grid->column('id_etudiant', __('Id etudiant'));
        $grid->column('id_budget', __('Id budget'));
        $grid->column('id_ligne_budgetaire_entree', __('Id ligne budgetaire entree'));
        $grid->column('id_element_ligne_budgetaire_entree', __('Id element ligne budgetaire entree'));
        $grid->column('id_donnee_ligne_budgetaire_entree', __('Id donnee ligne budgetaire entree'));
        $grid->column('montant_reglement', __('Montant reglement'));
        $grid->column('reste_reglement', __('Reste reglement'));
        $grid->column('numero_reglement', __('Numero reglement'));
        $grid->column('date_reglement', __('Date reglement'));
        $grid->column('id_annee_academique', __('Id annee academique'));
        $grid->column('type_reglement', __('Type reglement'));
        $grid->column('id_user', __('Id user'));
        $grid->column('id_facture_etudiant', __('Id facture etudiant'));
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
        $show = new Show(reglement_etudiant::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('id_cycle', __('Id cycle'));
        $show->field('id_niveau', __('Id niveau'));
        $show->field('id_caisse', __('Id caisse'));
        $show->field('id_banque', __('Id banque'));
        $show->field('id_filière', __('Id filière'));
        $show->field('id_scolarité', __('Id scolarité'));
        $show->field('id_frais', __('Id frais'));
        $show->field('id_tranche_scolarite', __('Id tranche scolarite'));
        $show->field('id_specialite', __('Id specialite'));
        $show->field('id_etudiant', __('Id etudiant'));
        $show->field('id_budget', __('Id budget'));
        $show->field('id_ligne_budgetaire_entree', __('Id ligne budgetaire entree'));
        $show->field('id_element_ligne_budgetaire_entree', __('Id element ligne budgetaire entree'));
        $show->field('id_donnee_ligne_budgetaire_entree', __('Id donnee ligne budgetaire entree'));
        $show->field('montant_reglement', __('Montant reglement'));
        $show->field('reste_reglement', __('Reste reglement'));
        $show->field('numero_reglement', __('Numero reglement'));
        $show->field('date_reglement', __('Date reglement'));
        $show->field('id_annee_academique', __('Id annee academique'));
        $show->field('type_reglement', __('Type reglement'));
        $show->field('id_user', __('Id user'));
        $show->field('id_facture_etudiant', __('Id facture etudiant'));
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
        $form = new Form(new reglement_etudiant());

        $form->number('id_cycle', __('Id cycle'));
        $form->number('id_niveau', __('Id niveau'));
        $form->number('id_caisse', __('Id caisse'));
        $form->number('id_banque', __('Id banque'));
        $form->number('id_filière', __('Id filière'));
        $form->number('id_scolarité', __('Id scolarité'));
        $form->number('id_frais', __('Id frais'));
        $form->number('id_tranche_scolarite', __('Id tranche scolarite'));
        $form->number('id_specialite', __('Id specialite'));
        $form->number('id_etudiant', __('Id etudiant'));
        $form->number('id_budget', __('Id budget'));
        $form->number('id_ligne_budgetaire_entree', __('Id ligne budgetaire entree'));
        $form->number('id_element_ligne_budgetaire_entree', __('Id element ligne budgetaire entree'));
        $form->number('id_donnee_ligne_budgetaire_entree', __('Id donnee ligne budgetaire entree'));
        $form->decimal('montant_reglement', __('Montant reglement'));
        $form->decimal('reste_reglement', __('Reste reglement'));
        $form->number('numero_reglement', __('Numero reglement'));
        $form->date('date_reglement', __('Date reglement'))->default(date('Y-m-d'));
        $form->number('id_annee_academique', __('Id annee academique'));
        $form->number('type_reglement', __('Type reglement'));
        $form->number('id_user', __('Id user'));
        $form->number('id_facture_etudiant', __('Id facture etudiant'));

        return $form;
    }
}
