<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PublishedConcertConcert extends Controller
{
    public function store()
    {

        $conert = auth()->user()
            ->concerts()
            ->findOrFail(request()->concert_id);

        if ($conert->isPublished()) {
            abort(422);
        }

        $conert->publish();



        return redirect()->back();
    }
}
