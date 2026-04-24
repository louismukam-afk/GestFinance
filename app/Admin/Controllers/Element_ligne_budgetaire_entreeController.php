<?php

namespace App\Admin\Controllers;

use App\Models\element_ligne_budgetaire_entree;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class Element_ligne_budgetaire_entreeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'element_ligne_budgetaire_entree';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new element_ligne_budgetaire_entree());

        $grid->column('id', __('Id'));
        $grid->column('libelle_elements_ligne_budgetaire_entree', __('Libelle elements ligne budgetaire entree'));
        $grid->column('code_elements_ligne_budgetaire_entree', __('Code elements ligne budgetaire entree'));
        $grid->column('numero_compte_elements_ligne_budgetaire_entree', __('Numero compte elements ligne budgetaire entree'));
        $grid->column('description', __('Description'));
        $grid->column('date_creation', __('Date creation'));
        $grid->column('id_igne_budgetaire_entree', __('Id igne budgetaire entree'));
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
        $show = new Show(element_ligne_budgetaire_entree::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('libelle_elements_ligne_budgetaire_entree', __('Libelle elements ligne budgetaire entree'));
        $show->field('code_elements_ligne_budgetaire_entree', __('Code elements ligne budgetaire entree'));
        $show->field('numero_compte_elements_ligne_budgetaire_entree', __('Numero compte elements ligne budgetaire entree'));
        $show->field('description', __('Description'));
        $show->field('date_creation', __('Date creation'));
        $show->field('id_igne_budgetaire_entree', __('Id igne budgetaire entree'));
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
        $form = new Form(new element_ligne_budgetaire_entree());

        $form->text('libelle_elements_ligne_budgetaire_entree', __('Libelle elements ligne budgetaire entree'));
        $form->text('code_elements_ligne_budgetaire_entree', __('Code elements ligne budgetaire entree'));
        $form->text('numero_compte_elements_ligne_budgetaire_entree', __('Numero compte elements ligne budgetaire entree'));
        $form->text('description', __('Description'));
        $form->date('date_creation', __('Date creation'))->default(date('Y-m-d'));
        $form->number('id_igne_budgetaire_entree', __('Id igne budgetaire entree'));
        $form->number('id_user', __('Id user'));

        return $form;
    }
}
