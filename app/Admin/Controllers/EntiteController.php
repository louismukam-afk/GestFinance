<?php

namespace App\Admin\Controllers;

use App\Models\entite;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class EntiteController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'entite';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new entite());

        $grid->column('id', __('Id'));
        $grid->column('nom_entite', __('Nom entite'));
        $grid->column('localisation', __('Localisation'));
        $grid->column('telephone', __('Telephone'));
        $grid->column('email', __('Email'));
        $grid->column('description', __('Description'));
        $grid->column('logo', __('Logo'));
        $grid->column('users.name', __('User'));
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
        $show = new Show(entite::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('nom_entite', __('Nom entite'));
        $show->field('localisation', __('Localisation'));
        $show->field('telephone', __('Telephone'));
        $show->field('email', __('Email'));
        $show->field('description', __('Description'));
        $show->field('logo', __('Logo'));
        $show->field('id_user', __('User'));
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
        $form = new Form(new entite());

        $form->text('nom_entite', __('Nom entite'));
        $form->text('localisation', __('Localisation'));
        $form->text('telephone', __('Telephone'));
        $form->email('email', __('Email'));
        $form->text('description', __('Description'));
        $form->file('logo', __('Logo'));
        $form->hidden('id_user');

        $form->saving(function (Form $form) {
            $form->id_user = \Admin::user()->id; // ou Auth::id() si hors de Laravel Admin
        });

        return $form;
    }
}
