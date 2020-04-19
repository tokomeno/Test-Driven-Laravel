<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class ConcertsController extends Controller
{

    public function index()
    {

        return view('backstage.concerts.index', [
            'publishedConcerts' => auth()->user()->concerts()->published()->get(),
            'unpublishedConcerts' => auth()->user()->concerts()->notPublished()->get(),
        ]);
    }


    public function create()
    {
        return view('backstage.concerts.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
        ]);

        /** @var Concert $concert */
        $concert = auth()->user()->concerts()->create([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'date' =>  Carbon::parse(vsprintf('%s %s', [
                $request->date,
                $request->time
            ])),
            'venue' =>  $request->venue,
            'venue_address' =>  $request->venue_address,
            'city' =>  $request->city,
            'state' =>  $request->state,
            'zip' =>  $request->zip,
            'ticket_price' =>  $request->ticket_price * 100,
            'additional_information' => $request->additional_information
            // 'ticket_quantity' =>  $request->ticket_quantity,
        ])
            ->addTickets($request->ticket_quantity)->publish();


        return redirect()->route('concerts.show', $concert);
    }
}
