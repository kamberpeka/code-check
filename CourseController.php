<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Hash;
use App\User;
use App\Course;
use App\Vendor;
use App\Category;
use App\Technology;
use Illuminate\Support\Str;
use App\Allcountrycourse;
use Auth;
use Validator;
use DB;

class CourseController extends Controller
{
    public function     index(){
    $data['courses'] =  Course::get();
    $data['categories'] = Category::where('parent_category',0)->get();
    $data['vendors'] = Vendor::get();
  	return view('admin.course',$data);
    }

    public function addcourse(){
    $data['courses'] =  Course::get();
    $data['categories'] = Category::where('parent_category',0)->get();
    $data['vendors'] = Vendor::get();
    $data['technologies'] = Technology::get();

    return view('admin.addcourse',$data);
    }


    public function savecourse(Request $request){
    $rules = array(
		    'title'    => 'required', // make sure the email is an actual email
		    'days'     => 'required',
		   // 'course_img' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
           );


		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
		    return redirect()->back()->withErrors($validator); 
		}    

    if($request->get('course_id')){
      if($request->get('check_country')){
        foreach ($request->get('check_country') as $key => $country_code) {
          $contry_course = Allcountrycourse::where('country_code',$country_code)->where('course_id',$request->get('course_id'))->first();
          if(empty($contry_course)){
            $contry_course = new Allcountrycourse();
          }
          $contry_course->course_id= $request->get('course_id');
          $contry_course->country_code= $country_code;
          $contry_course->price= $request->get('price');
          $contry_course->location1= $request->get('location1');
          $contry_course->location2= $request->get('location2');
          $contry_course->location3= $request->get('location3');
          $contry_course->location4= $request->get('location4');
          $contry_course->location5= $request->get('location5');
          $contry_course->date1 =   $request->date1  ? date('Y-m-d H:i:s',strtotime($request->date1)) : NULL;
          $contry_course->date2 =   $request->date2  ? date('Y-m-d H:i:s',strtotime($request->date2)) : NULL;
          $contry_course->date3 =   $request->date3  ? date('Y-m-d H:i:s',strtotime($request->date3)) : NULL;
          $contry_course->date4 =   $request->date4  ? date('Y-m-d H:i:s',strtotime($request->date4)) : NULL;
          $contry_course->date5 =   $request->date5  ? date('Y-m-d H:i:s',strtotime($request->date5)) : NULL;
          $contry_course->save();

          # code...
        }

      }
      $course = Course::find($request->get('course_id'));

    }else{

      $course = new Course();
    }

   // $customer = Customer::firstOrNew(['customer_id'=> $request->edit_id]);

        if ($files = $request->file('course_img')) {
           $destinationPath = public_path().'/images/course/'; // upload path
           $profileImage = $files->getClientOriginalName();
           $files->move($destinationPath, $profileImage);
           $course->course_img = "$profileImage";
        }
        if ($files = $request->file('top_banner')) {
           $destinationPath = public_path().'/images/course/'; // upload path
           $profileImage = $files->getClientOriginalName();
           $files->move($destinationPath, $profileImage);
           $course->top_banner = "$profileImage";
        }

    $course->title     = $request->get('title');
    $slug  = Str::slug($request->get('title'), "-");

  if (Course::where('slug',$slug)->exists()) {
       $max_id = Course::max('id')+1;
       $course->slug     = $slug.$max_id;
} else {    $course->slug     = $slug ;}

    $course->overview  = $request->get('overview');
	  $course->objective  = $request->get('objective');
	  $course->outline  = $request->get('outline');
	  $course->target_audience  = $request->get('target_audience');
	  $course->prerequisite  = $request->get('prerequisite');
	  $course->location1  = $request->get('location1');

    $course->days =    $request->days  ? $request->days  : 0 ;
    $course->price =   $request->price ? $request->price : 0.00;
    $course->date1 =   $request->date1  ? date('Y-m-d H:i:s',strtotime($request->date1)) : NULL;
    $course->date2 =   $request->date2  ? date('Y-m-d H:i:s',strtotime($request->date2)) : NULL;
    $course->date3 =   $request->date3  ? date('Y-m-d H:i:s',strtotime($request->date3)) : NULL;
    $course->date4 =   $request->date4  ? date('Y-m-d H:i:s',strtotime($request->date4)) : NULL;
    $course->date5 =   $request->date5  ? date('Y-m-d H:i:s',strtotime($request->date5)) : NULL;

		$course->location2  = $request->get('location2');
		$course->location3  = $request->get('location3');
		$course->location4  = $request->get('location4');
		$course->location5  = $request->get('location5');
		$course->category  = $request->get('category');
		$course->vendor  = $request->get('vendor');
		$course->technology   = $request->get('technology');

        $course->seo_title   = $request->get('seo_title');
        $course->seo_keyword   = $request->get('seo_keyword');
        $course->seo_description   = $request->get('seo_description');
		    
        $course->save();
        return redirect()->route('admin.course')->with('success','Course Added successfully.');
      }

      public function  deletecourse(Request $request){
      
            $course = new Course();
            $allcountrycourse = new Allcountrycourse();
            
            $course->where('id',$request->course_id)->delete();
            $allcountrycourse->where('course_id',$request->course_id)->delete();
            
             return redirect()->route('admin.course');
      
      }
      
      public function  editcourse($id){
        $data['course'] = Course::find($id);
        $data['categories'] = Category::where('parent_category',0)->get();
        $data['vendors'] = Vendor::get();
        $data['technologies'] = Technology::get();
        return view('admin.editcourse',$data);
      }


      public function  editcountrycourse($country_code,$id){
       
        $data['country_course'] = Allcountrycourse::where('course_id', $id)->where('country_code',$country_code)->first(); 
        
        $data['course'] = Course::find($id);

        $data['categories'] = Category::where('parent_category',0)->get();
        $data['vendors'] = Vendor::get();
        $data['technologies'] = Technology::get();
        $data['countrycode'] =  $country_code ;
        return view('admin.editcountrycourse',$data);
      }

     public function updatecountrycourse(Request $request)
     {
     
        $allcountrycourse = Allcountrycourse::where('course_id', $request->get('course_id'))->where('country_code',$request->get('country_code'))->first(); 
        

      if(empty($allcountrycourse)){
        $allcountrycourse = new Allcountrycourse();
      }


   
    //$Allcountrycourse = Allcountrycourse::firstOrNew(['course_id'=> $request->course_id]);

    $allcountrycourse->date1 =   $request->date1  ? date('Y-m-d H:i:s',strtotime($request->date1)) : NULL;
    $allcountrycourse->date2 =   $request->date2  ? date('Y-m-d H:i:s',strtotime($request->date2)) : NULL;
    $allcountrycourse->date3 =   $request->date3  ? date('Y-m-d H:i:s',strtotime($request->date3)) : NULL;
    $allcountrycourse->date4 =   $request->date4  ? date('Y-m-d H:i:s',strtotime($request->date4)) : NULL;
    $allcountrycourse->date5 =   $request->date5  ? date('Y-m-d H:i:s',strtotime($request->date5)) : NULL;

    $allcountrycourse->price =   $request->price ? $request->price : 0.00;

    $allcountrycourse->location1  = $request->get('location1');
    $allcountrycourse->location2  = $request->get('location2');
    $allcountrycourse->location3  = $request->get('location3');
    $allcountrycourse->location4  = $request->get('location4');
    $allcountrycourse->location5  = $request->get('location5');

    $allcountrycourse->course_id  = $request->get('course_id');
    $allcountrycourse->country_code  = $request->get('country_code');


    $allcountrycourse->save();
    return redirect()->back()->with('editsucces','Update Cource Successfully.');
     } 

   public function exportcoursedata()
   { 
        $data['course'] = Course::get();
        $data['categories'] = Category::get();
        $data['vendors'] = Vendor::get();
        $data['technologies'] = Technology::get();

   
   $fileName = 'courses.csv';
   $courses = Course::all();

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('id', 'title','overview','objective','outline','target_audience','prerequisite','days','price','category','vendor','technology','seo_title','seo_keyword','seo_description','date1','date2','date3','date4','date5','location1','location2','location3','location4','location5');

        $callback = function() use($courses, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($courses as $course) {
                $course['id']  = $course->id;
                $course['title']    = $course->title;
                 $course['overview']    = $course->overview;
                 $course['objective']    = $course->objective;
                 $course['outline']    = $course->outline;
                 $course['target_audience']    = $course->target_audience;
                 $course['prerequisite']    = $course->prerequisite;
                 $course['days']    = $course->days;
                 $course['price']    = $course->price;
                 $course['seo_title']    = $course->seo_title;
                 $course['seo_keyword']    = $course->seo_keyword;
                 $course['seo_description']    = $course->seo_description;
                 $course['category']    = $course->category;
                 $course['technology']    = $course->technology;
                 $course['vendor']    = $course->vendor;
                 $course['date1']    = date("Y/m/d",strtotime($course->date1));       
                 $course['date2']    = date("Y/m/d",strtotime($course->date2));
                 $course['date3']    = date("Y/m/d",strtotime($course->date3));
                 $course['date4']    = date("Y/m/d",strtotime($course->date4));
                 $course['date5']    = date("Y/m/d",strtotime($course->date5));
                 $course['location1']    = $course->location1;
                 $course['location2']    = $course->location2;
                 $course['location3']    = $course->location3;
                 $course['location4']    = $course->location4;
                 $course['location5']    = $course->location5;
                
                fputcsv($file, array($course['id'], $course['title'],$course['overview'],$course['objective'],$course['outline'],$course['target_audience'],$course['prerequisite'],$course['days'],$course['price'],$course['category'],$course['vendor'],$course['technology'],$course['seo_title'],$course['seo_keyword'],$course['seo_description'],$course['date1'],$course['date2'],$course['date3'],$course['date4'],$course['date5'],$course['location1'],$course['location2'],$course['location3'],$course['location4'],$course['location5']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);

    }

      public function importcoursedata(Request $request)
   { 
      $file = $request->file('course_csv');

      // File Details 
      $filename = $file->getClientOriginalName();
      $extension = $file->getClientOriginalExtension();
      $tempPath = $file->getRealPath();
      $fileSize = $file->getSize();
      $mimeType = $file->getMimeType();

      // Valid File Extensions
      $valid_extension = array("csv");

      // 2MB in Bytes
      $maxFileSize = 2097152; 

      // Check file extension
      if(in_array(strtolower($extension),$valid_extension)){

        // Check file size
        if($fileSize <= $maxFileSize){

          // File upload location
          $location = public_path().'/images/csv/'; // upload path

          // Upload file
          $file->move($location,$filename);

          // Import CSV to Database
          $filepath = public_path("/images/csv/".$filename);
         
         
          // Reading file
          $file = fopen($filepath,"r");

          $importData_arr = array();
          $i = 0;

          while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
             $num = count($filedata );
             
             // Skip first row (Remove below comment if you want to skip the first row)
             if($i == 0){
                $i++;
                continue; 
             }
             for ($c=0; $c < $num; $c++) {
                $importData_arr[$i][] = $filedata [$c];
             }
             $i++;
          }
          fclose($file);

          // Insert to MySQL database
          foreach($importData_arr as $importData){

          
                $date1 = date('Y-m-d H:i:s',strtotime(str_replace("/","-",$importData[15])));  
                $date2 = date('Y-m-d H:i:s',strtotime(str_replace("/","-",$importData[16])));
                $date3 = date('Y-m-d H:i:s',strtotime(str_replace("/","-",$importData[17])));
                $date4 = date('Y-m-d H:i:s',strtotime(str_replace("/","-",$importData[18])));
                $date5 = date('Y-m-d H:i:s',strtotime(str_replace("/","-",$importData[19])));

         DB::update('update course set title = ?, price=? , overview=?,objective=?,outline=?,target_audience=?,prerequisite=?,days=?,category=?,vendor=?,technology=?,seo_title=?,seo_keyword=?,seo_description=?,date1=?,date2=?,date3=?,date4=?,date5=?,location1=?,location2=?,location3=?,location4=?,location5=?  where id = ?',[$importData[1],$importData[8],$importData[2],$importData[3],$importData[4],$importData[5],$importData[6],$importData[7],$importData[10],$importData[9],$importData[11],$importData[12],$importData[13],$importData[14],$date1,$date2,$date3,$date4,$date5,$importData[20],$importData[21],$importData[22],$importData[23],$importData[24],$importData[0]]); 
     
      
          }
          

            return redirect()->back()->with('importsucess','Import Course data Successfully.');

        }else{
             return redirect()->back()->with('importsucess','File too large. File must be less than 2MB.');
        }

      }else{
               return redirect()->back()->with('importsucess','Invalid File Extension.');

      }
     
   }

   public function exportcountrycoursedata($country_code)
   { 
  
   //$q = DB::table('all_country_course')->select('all_country_course.*','course.title')->join('course','course.id','=','all_country_course.course_id')->where(['country_code' => $country_code])->get();


    $fileName = $country_code.'_'.'courses.csv';
    $courses = Allcountrycourse::where('country_code',$country_code)->get();
    $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('country_course_id','date1','date2','date3','date4','date5','location1','location2','location3','location4','location5','price','course_id','country_code');

        $callback = function() use($courses, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

                  foreach ($courses as $course) {
            
                $course['country_course_id']  = $course->country_course_id;
                 $course['price']    = $course->price;
                 $course['date1']    = date("Y/m/d",strtotime($course->date1));       
                 $course['date2']    = date("Y/m/d",strtotime($course->date2));
                 $course['date3']    = date("Y/m/d",strtotime($course->date3));
                 $course['date4']    = date("Y/m/d",strtotime($course->date4));
                 $course['date5']    = date("Y/m/d",strtotime($course->date5)); 
                 $course['location1']    = $course->location1;
                 $course['location2']    = $course->location2;
                 $course['location3']    = $course->location3;
                 $course['location4']    = $course->location4;
                 $course['location5']    = $course->location5;
                 $course['course_id']    = $course->course_id;
                 $course['country_code']    = $course->country_code;                 
                fputcsv($file, array($course['country_course_id'],$course['date1'],$course['date2'],$course['date3'],$course['date4'],$course['date5'],$course['location1'],$course['location2'],$course['location3'],$course['location4'],$course['location5'],$course['price'],$course['course_id'],$course['country_code']));
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
   }

  public function importcountrycoursedata(Request $request)
   { 
      $file = $request->file('country_course_csv');

      // File Details 
      $filename = $file->getClientOriginalName();
      $extension = $file->getClientOriginalExtension();
      $tempPath = $file->getRealPath();
      $fileSize = $file->getSize();
      $mimeType = $file->getMimeType();

      // Valid File Extensions
      $valid_extension = array("csv");
      // 2MB in Bytes
      $maxFileSize = 2097152; 
      // Check file extension
      if(in_array(strtolower($extension),$valid_extension)){
       // Check file size
        if($fileSize <= $maxFileSize){

          // File upload location
          $location = public_path().'/images/csv/'; // upload path

          // Upload file
          $file->move($location,$filename);

          // Import CSV to Database
          $filepath = public_path("/images/csv/".$filename);
         
         
          // Reading file
          $file = fopen($filepath,"r");

          $importData_arr = array();
          $i = 0;

          while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
             $num = count($filedata );
             
             // Skip first row (Remove below comment if you want to skip the first row)
             if($i == 0){                $i++;   continue; }
             for ($c=0; $c < $num; $c++) {    $importData_arr[$i][] = $filedata [$c]; }
             $i++;
             }
          fclose($file);

          // Insert to MySQL database
          foreach($importData_arr as $importData){
            
                $date1 = date('Y-m-d H:i:s',strtotime(str_replace("/","-",$importData[1])));  
                $date2 = date('Y-m-d H:i:s',strtotime(str_replace("/","-",$importData[2])));
                $date3 = date('Y-m-d H:i:s',strtotime(str_replace("/","-",$importData[3])));
                $date4 = date('Y-m-d H:i:s',strtotime(str_replace("/","-",$importData[4])));
                $date5 = date('Y-m-d H:i:s',strtotime(str_replace("/","-",$importData[5])));
        
     
          DB::update('update all_country_course set  date1 =?,date2 =?,date3 =?,date4 =?,date5 =?,location1=?,location2=?,location3=?,location4=?,location5=?,price=? where country_course_id = ?',[$date1,$date2,$date3,$date4,$date5,$importData[6],$importData[7],$importData[8],$importData[9],$importData[10],$importData[11],$importData[0]]); 

          }
             return redirect()->back()->with('importsucess','Import Course data Successfully.');
        }else{
           return redirect()->back()->with('importsucess','File too large. File must be less than 2MB.');
        }

      }else{
           return redirect()->back()->with('importsucess','Invalid File Extension');
      }
     
   }


}
