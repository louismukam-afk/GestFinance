<?php

namespace App\Admin\Controllers;

use App\Models\ligne_budgetaire_Entree;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class Ligne_budgetaire_EntreeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'ligne_budgetaire_Entree';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ligne_budgetaire_Entree());

        $grid->column('id', __('Id'));
        $grid->column('libelle_ligne_budgetaire_entree', __('Libelle ligne budgetaire entree'));
        $grid->column('code_ligne_budgetaire_entree', __('Code ligne budgetaire entree'));
        $grid->column('numero_compte_ligne_budgetaire_entree', __('Numero compte ligne budgetaire entree'));
        $grid->column('description', __('Description'));
        $grid->column('date_creation', __('Date creation'));
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
        $show = new Show(ligne_budgetaire_Entree::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('libelle_ligne_budgetaire_entree', __('Libelle ligne budgetaire entree'));
        $show->field('code_ligne_budgetaire_entree', __('Code ligne budgetaire entree'));
        $show->field('numero_compte_ligne_budgetaire_entree', __('Numero compte ligne budgetaire entree'));
        $show->field('description', __('Description'));
        $show->field('date_creation', __('Date creation'));
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
        $form = new Form(new ligne_budgetaire_Entree());

        $form->text('libelle_ligne_budgetaire_entree', __('Libelle ligne budgetaire entree'));
        $form->text('code_ligne_budgetaire_entree', __('Code ligne budgetaire entree'));
        $form->text('numero_compte_ligne_budgetaire_entree', __('Numero compte ligne budgetaire entree'));
        $form->text('description', __('Description'));
        $form->date('date_creation', __('Date creation'))->default(date('Y-m-d'));
        $form->number('id_user', __('Id user'));

        return $form;
    }
}
