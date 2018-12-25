<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class UsersController extends Controller
{
    use HasResourceActions;

    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('用户列表');
            $content->body($this->grid());
        });
    }

    protected function grid()
    {
        return Admin::grid(User::class, function (Grid $grid) {
            $grid->id('ID')->sortable();

            $grid->name('用户名');

            $grid->email('邮箱');

            $grid->email_verified('已验证邮箱')->display(function ($value) {
                return $value ? '是' : '否';
            });

            $grid->created_at('注册时间')->sortable();

            $grid->disableCreateButton();

            $grid->actions(function ($actions) {
                $actions->disableView();
                $actions->disableEdit();
                $actions->disableDelete();
            });

            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });
        });
    }
}
