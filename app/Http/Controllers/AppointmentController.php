<?php

namespace App\Http\Controllers;

use App\Appointment;
use Illuminate\Http\Request;
use App\Http\Resources\Appointment as AppointmentResource;
use App\Http\Resources\AppointmentCollection;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //return new AppointmentCollection(Appointment::all());
        $all = Appointment::all();
        if ($all!=null) { return new AppointmentCollection($all); } else {
            return response()->json(['error' => 'Nothing found.'], 404); 
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $date = $request->date; $hour = $request->hour; $email = $request->email;

        if (empty($date) || empty($hour) || empty($email)) {     
            return response()->json(['warning' => 'You must provide of all the information required for the appointment.'], 200);
        } else {
            //$fulldate = date("Y-m-d H:i:s", strtotime($date.' '.$hour));

            $date = date("Y-m-d", strtotime($date));
            $hour = date("H:i:s", strtotime($hour));

            $dayweek = date('w', strtotime($date));
            if ($dayweek == 0 || $dayweek == 6) {
                return response()->json(['warning' => 'The schedule must be set for office hours (Monday to Friday).'], 200);
            }

            $hoursday = date('H', strtotime($hour));
            if ($hoursday < 9 || $hoursday > 17) {
                return response()->json(['warning' => 'The schedule must be set for office hours (9 am to 6 pm).'], 200);
            }

            //$end = date("Y-m-d H:i:s", (strtotime($fulldate) + 3600)); // Add 1 hour
            $end = date("H:i:s", (strtotime($hour) + 3600)); // Add 1 hour

            //$existsapm = Appointment::where('start', $fulldate)->first();
            //$existsapm = Appointment::whereColumn([ ['date', $date], ['start', $hour] ])->first();
            $existsapm = Appointment::where('date', $date)->where('start', $hour)->first();

            if ($existsapm != null) {
                return response()->json(['warning' => 'The hour is taken, please choose another one.'], 200);
            } else {
                //$apm = Appointment::create(['start' => $fulldate, 'end' => $end, 'email' => $email]);
                $apm = Appointment::create(['date' => $date, 'start' => $hour, 'end' => $end, 'email' => $email]);
                return (new AppointmentResource($apm))->additional(['message' => ['success' => 'Appointment created.']]);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //return new AppointmentResource(Appointment::findOrFail($id));
        $show = Appointment::find($id);
        if ($show!=null) { return new AppointmentResource($show); } else {
            return response()->json(['error' => 'Nothing found.'], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function edit(Appointment $appointment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //$updateapm = Appointment::findOrFail($id);
        $updateapm = Appointment::find($id);

        if ($updateapm!=null){            
            $updateapm->date = $request->date;
            $updateapm->start = $request->start;
            $updateapm->end = $request->end;
            $updateapm->email = $request->email;
            $updateapm->save();
            return (new AppointmentResource($updateapm))->additional(['message' => ['success' => 'Appointment updated.']]);
        } else {
            return response()->json(['error' => 'Appointment not found.'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deleteapm = Appointment::findOrFail($id);
        $deleteapm->delete();
        return response()->json(['success' => 'Appointment deleted.'], 200);
    }
}
