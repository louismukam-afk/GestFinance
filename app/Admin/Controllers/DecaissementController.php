<?php

namespace App\Admin\Controllers;

use App\Models\decaissement;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DecaissementController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'decaissement';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new decaissement());

        $grid->column('id', __('Id'));
        $grid->column('id_ligne_budgetaire_sortie', __('Id ligne budgetaire sortie'));
        $grid->column('id_elements_ligne_budgetaire_sortie', __('Id elements ligne budgetaire sortie'));
        $grid->column('id_donnee_ligne_budgetaire_sortie', __('Id donnee ligne budgetaire sortie'));
        $grid->column('id_donnee_budgetaire_sortie', __('Id donnee budgetaire sortie'));
        $grid->column('id_caisse', __('Id caisse'));
        $grid->column('id_banque', __('Id banque'));
        $grid->column('id_bon_commande', __('Id bon commande'));
        $grid->column('numero_depense', __('Numero depense'));
        $grid->column('motif', __('Motif'));
        $grid->column('date_depense', __('Date depense'));
        $grid->column('id_budget', __('Id budget'));
        $grid->column('montant', __('Montant'));
        $grid->column('id_user', __('Id user'));
        $grid->column('id_personnel', __('Id personnel'));
        $grid->column('id_annee_academique', __('Id annee academique'));
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
        $show = new Show(decaissement::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('id_ligne_budgetaire_sortie', __('Id ligne budgetaire sortie'));
        $show->field('id_elements_ligne_budgetaire_sortie', __('Id elements ligne budgetaire sortie'));
        $show->field('id_donnee_ligne_budgetaire_sortie', __('Id donnee ligne budgetaire sortie'));
        $show->field('id_donnee_budgetaire_sortie', __('Id donnee budgetaire sortie'));
        $show->field('id_caisse', __('Id caisse'));
        $show->field('id_banque', __('Id banque'));
        $show->field('id_bon_commande', __('Id bon commande'));
        $show->field('numero_depense', __('Numero depense'));
        $show->field('motif', __('Motif'));
        $show->field('date_depense', __('Date depense'));
        $show->field('id_budget', __('Id budget'));
        $show->field('montant', __('Montant'));
        $show->field('id_user', __('Id user'));
        $show->field('id_personnel', __('Id personnel'));
        $show->field('id_annee_academique', __('Id annee academique'));
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
        $form = new Form(new decaissement());

        $form->number('id_ligne_budgetaire_sortie', __('Id ligne budgetaire sortie'));
        $form->number('id_elements_ligne_budgetaire_sortie', __('Id elements ligne budgetaire sortie'));
        $form->number('id_donnee_ligne_budgetaire_sortie', __('Id donnee ligne budgetaire sortie'));
        $form->number('id_donnee_budgetaire_sortie', __('Id donnee budgetaire sortie'));
        $form->number('id_caisse', __('Id caisse'));
        $form->number('id_banque', __('Id banque'));
        $form->number('id_bon_commande', __('Id bon commande'));
        $form->text('numero_depense', __('Numero depense'));
        $form->text('motif', __('Motif'));
        $form->date('date_depense', __('Date depense'))->default(date('Y-m-d'));
        $form->number('id_budget', __('Id budget'));
        $form->decimal('montant', __('Montant'));
        $form->number('id_user', __('Id user'));
        $form->number('id_personnel', __('Id personnel'));
        $form->number('id_annee_academique', __('Id annee academique'));

        return $form;
    }
}
