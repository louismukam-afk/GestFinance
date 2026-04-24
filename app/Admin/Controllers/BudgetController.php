<?php

namespace App\Admin\Controllers;

use App\Models\budget;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BudgetController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'budget';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new budget());

        $grid->column('id', __('Id'));
        $grid->column('libelle_ligne_budget', __('Libelle ligne budget'));
        $grid->column('date_debut', __('Date debut'));
        $grid->column('date_fin', __('Date fin'));
        $grid->column('id_user', __('Id user'));
        $grid->column('date_creation', __('Date creation'));
        $grid->column('montant_global', __('Montant global'));
        $grid->column('code_budget', __('Code budget'));
        $grid->column('description', __('Description'));
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
        $show = new Show(budget::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('libelle_ligne_budget', __('Libelle ligne budget'));
        $show->field('date_debut', __('Date debut'));
        $show->field('date_fin', __('Date fin'));
        $show->field('id_user', __('Id user'));
        $show->field('date_creation', __('Date creation'));
        $show->field('montant_global', __('Montant global'));
        $show->field('code_budget', __('Code budget'));
        $show->field('description', __('Description'));
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
        $form = new Form(new budget());

        $form->text('libelle_ligne_budget', __('Libelle ligne budget'));
        $form->date('date_debut', __('Date debut'))->default(date('Y-m-d'));
        $form->date('date_fin', __('Date fin'))->default(date('Y-m-d'));
        $form->number('id_user', __('Id user'));
        $form->date('date_creation', __('Date creation'))->default(date('Y-m-d'));
        $form->decimal('montant_global', __('Montant global'));
        $form->text('code_budget', __('Code budget'));
        $form->text('description', __('Description'));

        return $form;
    }
}
