<?php

namespace App\Http\ViewComposers\Setting;

use Illuminate\View\View;
use Illuminate\Http\Request;

use App\Models\Category;
use App\Models\Setting;

use Auth;


class AppSettingComposer
{
     
    /**
    * Instantiate a new instance of the Composer
    *
    * @param Illuminate\Http\Request
    */
    public function __construct(Request $request) {

    }

    /**
     * Bind data to the view
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view) {
        
        // Email Templates
        $email_templates = Setting::where('group', 'Email')
            ->orderBy('order')
            ->get();

        $admin_templates = Setting::where('group', 'Admin')
        ->orderBy('order')
        ->get();
        
        $template_groups = [];
        $admin_template_groups = [];

        foreach($email_templates as $template) {
            $template_groups[$template->sub_group][] = $template;
        }

        foreach($admin_templates as $template) {
            $admin_template_groups[$template->sub_group][] = $template;
        }
        
        $view->with(compact('template_groups','admin_template_groups'));
    }
}
