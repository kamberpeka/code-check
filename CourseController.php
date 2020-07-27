<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CourseStoreRequest;
use App\Services\CourseService;
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

/**
 * Rename CRUD functions as indicated on Laravel Docs (index, create, store, show, edit, update, destroy)
 * Views should be grouped within a folder by Model. In such case views should be under "admin/courses"
 *
 * On the controller constructor Dependency Injection is used for Repositories.
 *
 * All the methods should follow camelCase
 * All the Class Names should follow PascalCase
 */
class CourseController extends Controller
{
    /**
     * @var CourseRepositoryInterface
     */
    private $courseRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var VendorRepositoryInterface
     */
    private $vendorRepository;

    /**
     * @var TechnologyRepositoryInterface
     */
    private $technologyRepository;

    /**
     * @var AllCountryCourseRepositoryInterface
     */
    private $allCountryCodeRepository;

    /**
     * @param CourseRepositoryInterface $courseRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param VendorRepositoryInterface $vendorRepository
     * @param TechnologyRepositoryInterface $technologyRepository
     * @param AllCountryCourseRepositoryInterface $allCountryCodeRepository
     */
    public function __construct(
        CourseRepositoryInterface $courseRepository,
        CategoryRepositoryInterface $categoryRepository,
        VendorRepositoryInterface $vendorRepository,
        TechnologyRepositoryInterface $technologyRepository,
        AllCountryCourseRepositoryInterface $allCountryCodeRepository
    ) {
        $this->courseRepository = $courseRepository;
        $this->categoryRepository = $categoryRepository;
        $this->vendorRepository = $vendorRepository;
        $this->technologyRepository = $technologyRepository;
        $this->allCountryCodeRepository = $allCountryCodeRepository;
    }

    /**
     * Return only view from this function and then use the "data()" function to get courses using AJAX.
     * If Categories and Vendors are used for filtering, it is better to fetch using AJAX and create the functions on
     * the corresponding controller.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('admin.course');
    }

    /**
     * This function will be called using AJAX and it should return json object containing courses.
     * To transform data that will be returned we should create and return a CourseResource (Json resource) instance.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function data()
    {
        $courses = $this->courseRepository->paginate();

        return CourseResource::collection($courses);
    }

    /**
     * If the fetched data are used for select boxes, it is recommended to create 4 different endpoints
     * and fetch data on frontend using AJAX. In this way the performance would be a lot better because
     * data are fetched from DB only when needed!
     *
     * Also rename this function to "create"
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addcourse()
    {
        return view('admin.addcourse');
    }

    /**
     * Create a FormRequest and use it for data validation.
     * This way we clean the controller from validation rules, attributes and other checks!
     * Since the Service returns ServiceResponse object we can redirect with a proper message.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function savecourse(CourseStoreRequest $request)
    {
        $response = CourseService::store($request->all(), $request->file('course_img'), $request->file('top_banner'));

        return redirect()->route('admin.course')->with($response->getType(), $response->getMessage());
    }

    /**
     * Delete using Repository
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deletecourse(Request $request)
    {
        $this->courseRepository->findOrFail($request->course_id)->delete();
        $this->allCountryCodeRepository->filterByCourse($request->course_id)->delete();

        return redirect()->route('admin.course');
    }

    /**
     * Return only $course on the view and fetch other data used for select boxes using AJAX.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editcourse($id)
    {
        $course = $this->courseRepository->findOrFail($id);

        return view('admin.editcourse', compact('course'));
    }

    /**
     * Return only $country_course on the view and fetch other data used for select boxes using AJAX.
     * To get the course if needed fetch it as a relation from $country_course.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editcountrycourse($country_code, $id)
    {
        $country_course = $this->allCountryCodeRepository->findOrFailByCourseAndCode($id, $country_code);

        return view('admin.editcountrycourse', compact('country_course', 'country_code'));
    }

    /**
     * Use Service same way as store function
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * A Job must be created and used to export courses.
     * The job must be queued (with lowest priority) so the user doesn't have to wait until the file is exported
     * (if there are a lot of records it will take a few minutes until the export is finished).
     * After the export file is created save it on application storage, notify user and then show
     * all completed exports on a view where the user can download the files.
     * Protect files using middleware so the user can only view and download his exports.
     * Delete exported files after 15 days to clear storage.
     *
     * @return \Illuminate\Http\Response
     */
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

        $columns = [
            'id',
            'title',
            'overview',
            'objective',
            'outline',
            'target_audience',
            'prerequisite',
            'days',
            'price',
            'category',
            'vendor',
            'technology',
            'seo_title',
            'seo_keyword',
            'seo_description',
            'date1',
            'date2',
            'date3',
            'date4',
            'date5',
            'location1',
            'location2',
            'location3',
            'location4',
            'location5',
        ];

        $callback = function() use($courses, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($courses as $course) {
                $course['id']              = $course->id;
                $course['title']           = $course->title;
                $course['overview']        = $course->overview;
                $course['objective']       = $course->objective;
                $course['outline']         = $course->outline;
                $course['target_audience'] = $course->target_audience;
                $course['prerequisite']    = $course->prerequisite;
                $course['days']            = $course->days;
                $course['price']           = $course->price;
                $course['seo_title']       = $course->seo_title;
                $course['seo_keyword']     = $course->seo_keyword;
                $course['seo_description'] = $course->seo_description;
                $course['category']        = $course->category;
                $course['technology']      = $course->technology;
                $course['vendor']          = $course->vendor;
                $course['date1']           = date("Y/m/d", strtotime($course->date1));
                $course['date2']           = date("Y/m/d", strtotime($course->date2));
                $course['date3']           = date("Y/m/d", strtotime($course->date3));
                $course['date4']           = date("Y/m/d", strtotime($course->date4));
                $course['date5']           = date("Y/m/d", strtotime($course->date5));
                $course['location1']       = $course->location1;
                $course['location2']       = $course->location2;
                $course['location3']       = $course->location3;
                $course['location4']       = $course->location4;
                $course['location5']       = $course->location5;

                fputcsv($file, [
                    $course['id'],
                    $course['title'],
                    $course['overview'],
                    $course['objective'],
                    $course['outline'],
                    $course['target_audience'],
                    $course['prerequisite'],
                    $course['days'],
                    $course['price'],
                    $course['category'],
                    $course['vendor'],
                    $course['technology'],
                    $course['seo_title'],
                    $course['seo_keyword'],
                    $course['seo_description'],
                    $course['date1'],
                    $course['date2'],
                    $course['date3'],
                    $course['date4'],
                    $course['date5'],
                    $course['location1'],
                    $course['location2'],
                    $course['location3'],
                    $course['location4'],
                    $course['location5'],
                ]);
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
        if (in_array(strtolower($extension),$valid_extension)) {

            // Check file size
            if ($fileSize <= $maxFileSize) {

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

            } else {
                return redirect()->back()->with('importsucess','File too large. File must be less than 2MB.');
            }
        } else {
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

        $columns = [
            'country_course_id',
            'date1',
            'date2',
            'date3',
            'date4',
            'date5',
            'location1',
            'location2',
            'location3',
            'location4',
            'location5',
            'price',
            'course_id',
            'country_code',
        ];

        $callback = function() use($courses, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($courses as $course) {

                $course['country_course_id']  = $course->country_course_id;
                $course['price']        = $course->price;
                $course['date1']        = date("Y/m/d",strtotime($course->date1));
                $course['date2']        = date("Y/m/d",strtotime($course->date2));
                $course['date3']        = date("Y/m/d",strtotime($course->date3));
                $course['date4']        = date("Y/m/d",strtotime($course->date4));
                $course['date5']        = date("Y/m/d",strtotime($course->date5));
                $course['location1']    = $course->location1;
                $course['location2']    = $course->location2;
                $course['location3']    = $course->location3;
                $course['location4']    = $course->location4;
                $course['location5']    = $course->location5;
                $course['course_id']    = $course->course_id;
                $course['country_code'] = $course->country_code;

                fputcsv($file, [
                    $course['country_course_id'],
                    $course['date1'],
                    $course['date2'],
                    $course['date3'],
                    $course['date4'],
                    $course['date5'],
                    $course['location1'],
                    $course['location2'],
                    $course['location3'],
                    $course['location4'],
                    $course['location5'],
                    $course['price'],
                    $course['course_id'],
                    $course['country_code'],
                ]);
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
        if (in_array(strtolower($extension),$valid_extension)) {
            // Check file size
            if ($fileSize <= $maxFileSize) {

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
                foreach ($importData_arr as $importData) {

                    $date1 = date('Y-m-d H:i:s',strtotime(str_replace("/","-",$importData[1])));
                    $date2 = date('Y-m-d H:i:s',strtotime(str_replace("/","-",$importData[2])));
                    $date3 = date('Y-m-d H:i:s',strtotime(str_replace("/","-",$importData[3])));
                    $date4 = date('Y-m-d H:i:s',strtotime(str_replace("/","-",$importData[4])));
                    $date5 = date('Y-m-d H:i:s',strtotime(str_replace("/","-",$importData[5])));

                    DB::update('update all_country_course set  date1 =?,date2 =?,date3 =?,date4 =?,date5 =?,location1=?,location2=?,location3=?,location4=?,location5=?,price=? where country_course_id = ?',[$date1,$date2,$date3,$date4,$date5,$importData[6],$importData[7],$importData[8],$importData[9],$importData[10],$importData[11],$importData[0]]);
                }
                return redirect()->back()->with('importsucess', 'Import Course data Successfully.');
            } else {
                return redirect()->back()->with('importsucess', 'File too large. File must be less than 2MB.');
            }
        } else{
            return redirect()->back()->with('importsucess', 'Invalid File Extension');
        }
    }
}
