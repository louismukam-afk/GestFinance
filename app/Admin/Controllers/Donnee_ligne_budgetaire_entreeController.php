<?php

namespace App\Admin\Controllers;

use App\Models\donnee_ligne_budgetaire_entree;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class Donnee_ligne_budgetaire_entreeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'donnee_ligne_budgetaire_entree';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new donnee_ligne_budgetaire_entree());

        $grid->column('id', __('Id'));
        $grid->column('donnee_ligne_budgetaire_entree', __('Donnee ligne budgetaire entree'));
        $grid->column('code_donnee_ligne_budgetaire_entree', __('Code donnee ligne budgetaire entree'));
        $grid->column('numero_donne_ligne_budgetaire_entree', __('Numero donne ligne budgetaire entree'));
        $grid->column('description', __('Description'));
        $grid->column('date_creation', __('Date creation'));
        $grid->column('id_ligne_budgetaire_entree', __('Id ligne budgetaire entree'));
        $grid->column('id_budget', __('Id budget'));
        $grid->column('id_element_ligne_budgetaire_entree', __('Id element ligne budgetaire entree'));
        $grid->column('id_donnee_budgetaire_entree', __('Id donnee budgetaire entree'));
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
        $show = new Show(donnee_ligne_budgetaire_entree::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('donnee_ligne_budgetaire_entree', __('Donnee ligne budgetaire entree'));
        $show->field('code_donnee_ligne_budgetaire_entree', __('Code donnee ligne budgetaire entree'));
        $show->field('numero_donne_ligne_budgetaire_entree', __('Numero donne ligne budgetaire entree'));
        $show->field('description', __('Description'));
        $show->field('date_creation', __('Date creation'));
        $show->field('id_ligne_budgetaire_entree', __('Id ligne budgetaire entree'));
        $show->field('id_budget', __('Id budget'));
        $show->field('id_element_ligne_budgetaire_entree', __('Id element ligne budgetaire entree'));
        $show->field('id_donnee_budgetaire_entree', __('Id donnee budgetaire entree'));
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
        $form = new Form(new donnee_ligne_budgetaire_entree());

        $form->text('donnee_ligne_budgetaire_entree', __('Donnee ligne budgetaire entree'));
        $form->text('code_donnee_ligne_budgetaire_entree', __('Code donnee ligne budgetaire entree'));
        $form->text('numero_donne_ligne_budgetaire_entree', __('Numero donne ligne budgetaire entree'));
        $form->text('description', __('Description'));
        $form->date('date_creation', __('Date creation'))->default(date('Y-m-d'));
        $form->number('id_ligne_budgetaire_entree', __('Id ligne budgetaire entree'));
        $form->number('id_budget', __('Id budget'));
        $form->number('id_element_ligne_budgetaire_entree', __('Id element ligne budgetaire entree'));
        $form->number('id_donnee_budgetaire_entree', __('Id donnee budgetaire entree'));
        $form->number('id_user', __('Id user'));

        return $form;
    }
}
