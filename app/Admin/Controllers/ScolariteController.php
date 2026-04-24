<?php

namespace App\Admin\Controllers;

use App\Models\scolarite;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ScolariteController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'scolarite';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new scolarite());

        $grid->column('id', __('Id'));
        $grid->column('id_user', __('Id user'));
        $grid->column('id_cycle', __('Id cycle'));
        $grid->column('id_filiere', __('Id filiere'));
        $grid->column('id_niveau', __('Id niveau'));
        $grid->column('id_specialite', __('Id specialite'));
        $grid->column('montant_total', __('Montant total'));
        $grid->column('inscription', __('Inscription'));
        $grid->column('type_scolarite', __('Type scolarite'));
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
        $show = new Show(scolarite::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('id_user', __('Id user'));
        $show->field('id_cycle', __('Id cycle'));
        $show->field('id_filiere', __('Id filiere'));
        $show->field('id_niveau', __('Id niveau'));
        $show->field('id_specialite', __('Id specialite'));
        $show->field('montant_total', __('Montant total'));
        $show->field('inscription', __('Inscription'));
        $show->field('type_scolarite', __('Type scolarite'));
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
        $form = new Form(new scolarite());

        $form->number('id_user', __('Id user'));
        $form->number('id_cycle', __('Id cycle'));
        $form->number('id_filiere', __('Id filiere'));
        $form->number('id_niveau', __('Id niveau'));
        $form->number('id_specialite', __('Id specialite'));
        $form->decimal('montant_total', __('Montant total'));
        $form->decimal('inscription', __('Inscription'));
        $form->number('type_scolarite', __('Type scolarite'));

        return $form;
    }
}
