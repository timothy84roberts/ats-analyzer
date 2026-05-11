<?php

namespace App\Http\Controllers;

use App\Http\Requests\TestRequest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Process;

class HomeController extends Controller
{
    public function home(): View|Closure|string
    {
        $faker = Faker::create();
        $items = [];
        for ($i = 0; $i < 10; $i++) {
            $items[] = [
                'title' => $faker->jobTitle,
                'content' => $faker->catchPhrase,
                'imgUrl' => $faker->imageUrl
            ];
        }
        return view('home', compact('items'));
    }

    public function about(): View|Closure|string
    {
        $layout = 'layout.other';
        return view('about', compact('layout'));
    }

    public function contact(): View
    {
        return view('contact');
    }

    public function success(): View
    {
        return view('success');
    }

    public function sendContact(TestRequest $request)
    {
        $name = $request->name;
        $email = $request->email;
        $content = $request->content;
        return redirect()->route('contact.success');
    }
}
