<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use App\Models\Titletype;
use App\Admin\Extensions\ExcelExpoter;

class TitletypeController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('标题类型管理');
            $content->description('');

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        $r = isset($_GET['r'])?$_GET['r']:'';

        return Admin::content(function (Content $content) use ($id,$r) {

            $content->header('标题类型管理');
            $content->description('标题类型管理');


            if($r=='') $content->body($this->form()->edit($id));  
            
        });
    }


    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('添加标题类型');
            $content->description('添加标题类型');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Titletype::class, function (Grid $grid) {


            $grid->id('ID')->sortable();


            $grid->name('名称');
            $grid->alias('别名');
            $grid->sort('排序');
            $grid->link('链接');
            $grid->created_at('时间');
            $grid->actions(function ($actions) {
                $actions->disableDelete();
            });

            $grid->tools(function (Grid\Tools $tools) {
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                });
            });
            $grid->filter(function($filter){
                $filter->like('name', '名称');

            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id=null)
    {

        return Admin::form(Titletype::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('name','名称')->rules('required');
            $form->text('alias','别名');
            $form->text('link','链接');
            $form->number('sort','排序');
            $form->display('created_at', '创建时间');
            // $form->display('created_at', '创建时间');
            /*$form->disableSubmit();
            $form->disableReset();*/

        });
    }
}
