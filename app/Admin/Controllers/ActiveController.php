<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use App\Models\Activity;
use App\Models\Titletype;
use App\Models\Reward;
use App\Models\Scorerule;
use App\Models\Brand;
use App\Models\Danmu;
use App\Admin\Extensions\ExcelExpoter;

class ActiveController extends Controller
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

            $content->header('活动管理');
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

            $content->header('活动管理');
            $content->description('活动管理');

            if($r=='detail') $content->body($this->detail()->edit($id));
            if($r=='detail2') $content->body($this->detail2($id)->edit($id));
            if($r=='detail3') $content->body($this->detail3($id)->edit($id));
            if($r=='detail4') $content->body($this->detail4($id)->edit($id));
            if($r=='detail5') $content->body($this->detail5($id)->edit($id));
            if($r=='') $content->body($this->form()->edit($id));  
            
        });
    }


    protected function detail()
    {
        return Admin::form(Activity::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->display('title', '名称');
            $form->ueditor('detail','活动详情');
 

            // $form->display('created_at', '创建时间');
            /*$form->disableSubmit();
            $form->disableReset();*/

        });
    }
    protected function detail2()
    {
        return Admin::form(Activity::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->display('title', '名称');
            $form->ueditor('detail2','报名礼品');
 

            // $form->display('created_at', '创建时间');
            /*$form->disableSubmit();
            $form->disableReset();*/

        });
    }

    protected function detail3($id=null)
    {

        return Admin::form(Activity::class, function (Form $form)use($id) {
            $Activity = Activity::find($id);
            $form->display('id', 'ID');
            $form->display('title', '名称');

            $form->map($Activity->location_x,$Activity->location_y,'地址')->useGoogleMap();
 

            // $form->display('created_at', '创建时间');
            /*$form->disableSubmit();
            $form->disableReset();*/

        });
    }

    protected function detail4($id=null)
    {

        return Admin::form(Activity::class, function (Form $form)use($id) {
            $Activity = Activity::find($id);
            $form->display('id', 'ID');
            $form->display('title', '名称');

            $brand = Brand::where([])->select('id','name','alias')->orderBy('id','asc')->get()->toArray();
            $t = [];
            foreach ($brand as $key => $v) {
                $t[$v['id']] = $v['name'].'--'.$v['alias'];
            }
            $form->checkbox('brand', '品牌')->options($t);
        });
    }

    protected function detail5($id=null)
    {

        return Admin::form(Activity::class, function (Form $form)use($id) {
            $Activity = Activity::find($id);
            $form->display('id', 'ID');
            $form->display('title', '名称');

            $danmu = Danmu::where([])->select('id','name','alias')->orderBy('id','asc')->get()->toArray();
            $t = [];
            foreach ($danmu as $key => $v) {
                $t[$v['id']] = $v['name'].'--'.$v['alias'];
            }
            $form->checkbox('danmu', '弹幕')->options($t);
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

            $content->header('添加活动');
            $content->description('添加活动');

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
        return Admin::grid(Activity::class, function (Grid $grid) {


            $grid->id('ID')->sortable();
            $grid->title('名称');
            $grid->phone('手机号');
            $grid->zhuan_num('转发量');
            $grid->view_num('浏览量');
            $grid->sign_num('报名数');
            $grid->zhu_num('助力数');
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $id = $actions->row->id;
                // append一个操作
                $actions->append('<a href="/admin/active/'.$id.'/edit?r=detail" title="活动详情"><i class="fa fa-search-plus"></i></a>');
                $actions->append('<a href="/admin/active/'.$id.'/edit?r=detail2" title="报名礼品"><i class="fa fa-share"></i></a>');
                $actions->append('<a href="/admin/active/'.$id.'/edit?r=detail4" title="设置品牌"><i class="fa fa-beer"></i></a>');
                $actions->append('<a href="/admin/active/'.$id.'/edit?r=detail5" title="设置弹幕"><i class="fa fa-forumbee"></i></a>');

                // $actions->append('<a href="/admin/active/'.$id.'/edit?r=detail3" title="设置地图"><i class="fa fa-object-ungroup"></i></a>');

            });

            $grid->tools(function (Grid\Tools $tools) {
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                });
            });
            $grid->filter(function($filter){
                $filter->like('title', '名称');

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

        return Admin::form(Activity::class, function (Form $form)use($id) {

            $form->display('id', 'ID');
            $form->text('title', '名称')->rules('required');

            $form->multipleImage('pics', '多个配图')->move('pics/')->removable();
             $form->image('codepic', '活动二维码')->move('codepic/')->removable();


            $form->number('points', '热门指数');
            $form->number('zhuan_num', '转发数');
            $form->number('zhu_num', '助力数');
            $form->number('sign_num', '报名数');
            $form->number('view_num', '浏览量');
            $form->number('base', '数值基数');

            $form->text('phone', '手机号')->rules('required');
            $form->text('address', '地址')->rules('required');
            $form->text('location_x', '地址经度');
            $form->text('location_y', '地址纬度');
            // $form->map(39.916527,116.397128,'地址X,Y值');

            $form->datetime('start_at','开始时间')->format('YYYY-MM-DD HH:mm:ss')->rules('required');
            $form->datetime('end_at','结束时间')->format('YYYY-MM-DD HH:mm:ss')->rules('required');


           $form->file('mp3','背景音乐')->move('mp3/');

            $form->image('cust','客服二维码')->move('code/');
            $form->hidden('detail');
            $form->hidden('detail2');
            if($id){
                $form->display('brand','品牌')->with(function(){
                    $res = Brand::whereIn('id',$this->brand)->get()->toArray();
                    $res = array_column($res, 'name');
                    return $res?implode(',',$res):[];
                });

                $form->display('danmu','弹幕')->with(function(){
                    $res = Danmu::whereIn('id',$this->danmu)->get()->toArray();
                    $res = array_column($res, 'name');
                    return $res?implode(',',$res):[];
                });
            }
            
            $titletype = Titletype::where([])->select('id','name','alias')->orderBy('sort','asc')->get()->toArray();
            $t = [];
            foreach ($titletype as $key => $v) {
                $t[$v['id']] = $v['name'].'--'.$v['alias'];
            }

            $reward = Reward::where([])->select('id','name','alias','type')->orderBy('type','asc')->get()->toArray();
            $t2 = [];
            foreach ($reward as $key => $v) {
                $t2[$v['id']] = $v['name'].'--'.$v['alias'].'--'.$v['type'].'等奖';
            }

            $form->checkbox('titletype', '活动类型')->options($t);
            $form->checkbox('reward1', '积分报名奖品')->options($t2);
            $form->checkbox('reward2', '助力报名奖品')->options($t2);

            $scorerule = Scorerule::where([])->select('id','name','alias')->get()->toArray();
            $t3 = [];
            foreach ($scorerule as $key => $v) {
                $t3[$v['id']] = $v['name'].'--'.$v['alias'];
            }
            $form->checkbox('scorerule', '积分规则')->options($t3);
            $form->text('share_title','分享标题');
            $form->text('share_desc','分享内容');
            $form->image('share_pic','分享图片')->move('sharepic/')->removable();

            // $form->display('created_at', '创建时间');
            /*$form->disableSubmit();
            $form->disableReset();*/

        });
    }
}
