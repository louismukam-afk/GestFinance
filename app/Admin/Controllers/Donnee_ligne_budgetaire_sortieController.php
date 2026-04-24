<?php

namespace App\Admin\Controllers;

use App\Models\donnee_ligne_budgetaire_sortie;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class Donnee_ligne_budgetaire_sortieController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'donnee_ligne_budgetaire_sortie';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new donnee_ligne_budgetaire_sortie());

        $grid->column('id', __('Id'));
        $grid->column('donnee_ligne_budgetaire_sortie', __('Donnee ligne budgetaire sortie'));
        $grid->column('code_donnee_ligne_budgetaire_sortie', __('Code donnee ligne budgetaire sortie'));
        $grid->column('numero_donne_ligne_budgetaire_sortie', __('Numero donne ligne budgetaire sortie'));
        $grid->column('description', __('Description'));
        $grid->column('date_creation', __('Date creation'));
        $grid->column('id_ligne_budgetaire_sortie', __('Id ligne budgetaire sortie'));
        $grid->column('id_budget', __('Id budget'));
        $grid->column('id_element_ligne_budgetaire_sortie', __('Id element ligne budgetaire sortie'));
        $grid->column('id_donnee_budgetaire_sortie', __('Id donnee budgetaire sortie'));
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
        $show = new Show(donnee_ligne_budgetaire_sortie::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('donnee_ligne_budgetaire_sortie', __('Donnee ligne budgetaire sortie'));
        $show->field('code_donnee_ligne_budgetaire_sortie', __('Code donnee ligne budgetaire sortie'));
        $show->field('numero_donne_ligne_budgetaire_sortie', __('Numero donne ligne budgetaire sortie'));
        $show->field('description', __('Description'));
        $show->field('date_creation', __('Date creation'));
        $show->field('id_ligne_budgetaire_sortie', __('Id ligne budgetaire sortie'));
        $show->field('id_budget', __('Id budget'));
        $show->field('id_element_ligne_budgetaire_sortie', __('Id element ligne budgetaire sortie'));
        $show->field('id_donnee_budgetaire_sortie', __('Id donnee budgetaire sortie'));
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
        $form = new Form(new donnee_ligne_budgetaire_sortie());

        $form->text('donnee_ligne_budgetaire_sortie', __('Donnee ligne budgetaire sortie'));
        $form->text('code_donnee_ligne_budgetaire_sortie', __('Code donnee ligne budgetaire sortie'));
        $form->text('numero_donne_ligne_budgetaire_sortie', __('Numero donne ligne budgetaire sortie'));
        $form->text('description', __('Description'));
        $form->date('date_creation', __('Date creation'))->default(date('Y-m-d'));
        $form->number('id_ligne_budgetaire_sortie', __('Id ligne budgetaire sortie'));
        $form->number('id_budget', __('Id budget'));
        $form->number('id_element_ligne_budgetaire_sortie', __('Id element ligne budgetaire sortie'));
        $form->number('id_donnee_budgetaire_sortie', __('Id donnee budgetaire sortie'));
        $form->number('id_user', __('Id user'));

        return $form;
    }
}
