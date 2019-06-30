<?php

namespace App\Http\Controllers;

use App\Grade;
use App\Teacher;
use Carbon\Carbon;
use App\Attendence;

use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
    	$month = Attendence::select('attendence_date')->orderBy('attendence_date')->get()->groupBy(function ($val){
    		return Carbon::parse($val->attendence_date)->format('m');

    	});


    	if(request()->has(['type', 'month'])) {
    		$type = request()->input('type');
    		$month = request()->input('month');


    		if($type == 'class')
    		{
    			$attendences = Attendence::whereMonth('attendence_date', $month)->select('attendence_date', 'student_id', 'attendence_status', 'class_id')->orderBy('[class_id','attendence_date']);


    			return view('backend.attendence.index', compact('attendances', 'months'));
    		}
    	}

    	$attendences = [];

    	return view('backend.attendence.index', compact('attendences', 'months'));
    }



    public function createByTeacher($classid)
    {
    	$class = Grade::with(['students', 'subjects','teacher'])->findOrFails($classid);
    	return view('backend.attendance.create', compact('class'));
    }

    public function store(Request $request)
    {
    	$classid = $request->class_id;
    	$attenddate = date('Y-m-d');

    	$teacher = Teacher::findOrFail(auth()->user()->teacher->id);
    	$class = Grade::find($classid);


    	if($teacher->id !== $class->teacher_id) {
    		return redirect()->route('teacher.attendance.create', $classid)->with('status', 'You are not assign for this class attendence!');
    	}

    	$dataexist = Attendance::whereDate('attendence_date', $attenddate)->where('class_id', $classid)->get();

    	if(count($dataexist) 
    		!== 0 ) {
    		return redirect()->route('teacher.attendence.create', $classid)->with('status', 'Attendence already taken1');
    	}

    	$request->validate([
    		'class_id' => 'required|numeric',
    		'teacher_id' => 'required|numeric',
    		'attendences' => 'required'
    	]);

    	foreach($request->attendences as $studentid=>$attendence) {
    		if($attendence == 'present') {
    			$attendence_status = true;
    		} else if($attendence == 'absent') 
    		{
    			$attendence_status = false;
    		}

    		Attendence::create([
    			'class_id' => $request->class_id,
    			'teacher_id' => $request->teacher_id,
    			'student_id' => $studentid,
    			'attendence_date' => $attendate,
    			'attendence_status' => $attendence_status


    		]);
    	}

    	return back();

    	public function show(Attendence $attendance) {
    		$attendances = Attendance::where('student_id', $attendance->id)->get();
    		return view('backend.attendance.show', compact('attendances'));
    	}


    }
}
