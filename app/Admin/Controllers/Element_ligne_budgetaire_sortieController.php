<?php

namespace App\Admin\Controllers;

use App\Models\element_ligne_budgetaire_sortie;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class Element_ligne_budgetaire_sortieController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'element_ligne_budgetaire_sortie';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new element_ligne_budgetaire_sortie());

        $grid->column('id', __('Id'));
        $grid->column('libelle_elements_ligne_budgetaire_sortie', __('Libelle elements ligne budgetaire sortie'));
        $grid->column('code_elements_ligne_budgetaire_sortie', __('Code elements ligne budgetaire sortie'));
        $grid->column('numero_compte_elements_ligne_budgetaire_sortie', __('Numero compte elements ligne budgetaire sortie'));
        $grid->column('description', __('Description'));
        $grid->column('date_creation', __('Date creation'));
        $grid->column('id_ligne_budgetaire_sortie', __('Id ligne budgetaire sortie'));
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
        $show = new Show(element_ligne_budgetaire_sortie::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('libelle_elements_ligne_budgetaire_sortie', __('Libelle elements ligne budgetaire sortie'));
        $show->field('code_elements_ligne_budgetaire_sortie', __('Code elements ligne budgetaire sortie'));
        $show->field('numero_compte_elements_ligne_budgetaire_sortie', __('Numero compte elements ligne budgetaire sortie'));
        $show->field('description', __('Description'));
        $show->field('date_creation', __('Date creation'));
        $show->field('id_ligne_budgetaire_sortie', __('Id ligne budgetaire sortie'));
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
        $form = new Form(new element_ligne_budgetaire_sortie());

        $form->text('libelle_elements_ligne_budgetaire_sortie', __('Libelle elements ligne budgetaire sortie'));
        $form->text('code_elements_ligne_budgetaire_sortie', __('Code elements ligne budgetaire sortie'));
        $form->text('numero_compte_elements_ligne_budgetaire_sortie', __('Numero compte elements ligne budgetaire sortie'));
        $form->text('description', __('Description'));
        $form->date('date_creation', __('Date creation'))->default(date('Y-m-d'));
        $form->number('id_ligne_budgetaire_sortie', __('Id ligne budgetaire sortie'));
        $form->number('id_user', __('Id user'));

        return $form;
    }
}
