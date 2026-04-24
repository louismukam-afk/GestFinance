<?php

namespace App\Admin\Controllers;

use App\Models\annee_academique;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class anneeacademiqueController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'annee_academique';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new annee_academique());

        $grid->column('id', __('Id'));
        $grid->column('nom', __('Nom'));
        $grid->column('description', __('Description'));
        $grid->column('users.name', __('Utilisateur')); // Affiche le nom de l'utilisateur
        //$grid->column('id_user', __('Id user'));
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
        $show = new Show(annee_academique::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('nom', __('Nom'));
        $show->field('description', __('Description'));
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
        $form = new Form(new annee_academique());

        $form->text('nom', __('Nom'));
        $form->text('description', __('Description'));
        // Ici on cache le champ à l'utilisateur et on injecte la valeur plus tard
        $form->hidden('id_user');

        // Définir la valeur automatiquement lors de la sauvegarde
        $form->saving(function (Form $form) {
            $form->id_user = \Admin::user()->id; // ou Auth::id() si hors de Laravel Admin
        });
      //  $form->number('id_user', __('Id user'));

        return $form;
    }
}
