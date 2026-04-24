<?php

namespace App\Admin\Controllers;

use App\Models\tranche_scolarite;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class Tranche_scolariteController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'tranche_scolarite';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new tranche_scolarite());



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
        $show = new Show(tranche_scolarite::findOrFail($id));



        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new tranche_scolarite());



        return $form;
    }
}
