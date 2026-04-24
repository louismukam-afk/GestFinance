<?php

namespace App\Admin\Controllers;

use App\Models\role_utilisateur;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class Role_utilisateurController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'role_utilisateur';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new role_utilisateur());

        $grid->column('id', __('Id'));
        $grid->column('id_user', __('Id user'));
        $grid->column('value', __('Value'));
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
        $show = new Show(role_utilisateur::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('id_user', __('Id user'));
        $show->field('value', __('Value'));
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
        $form = new Form(new role_utilisateur());

        $form->number('id_user', __('Id user'));
        $form->decimal('value', __('Value'));

        return $form;
    }
}
