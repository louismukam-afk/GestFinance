<?php

namespace App\Admin\Controllers;

use App\Models\caisse;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CaisseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'caisse';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new caisse());

        $grid->column('id', __('Id'));
        $grid->column('nom_caisse', __('Nom caisse'));
        $grid->column('description', __('Description'));
        $grid->column('code_caisse', __('Code caisse'));
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
        $show = new Show(caisse::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('nom_caisse', __('Nom caisse'));
        $show->field('description', __('Description'));
        $show->field('code_caisse', __('Code caisse'));
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
        $form = new Form(new caisse());

        $form->text('nom_caisse', __('Nom caisse'));
        $form->text('description', __('Description'));
        $form->text('code_caisse', __('Code caisse'));
        $form->number('id_user', __('Id user'));

        return $form;
    }
}
