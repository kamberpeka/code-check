<?php

namespace App\Services;

use App\Repository\Contracts\CourseRepositoryInterface;
use App\Support\Classes\ServiceResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CourseService
{
    /**
     * @var CourseRepositoryInterface
     */
    private static $courseRepository;

    /**
     * @param CourseRepositoryInterface $courseRepository
     */
    public function __construct(CourseRepositoryInterface $courseRepository)
    {
        self::$courseRepository = $courseRepository;
    }

    /**
     * Store function is responsible for creating the course entity.
     * Using try catch is recommended so we do not show 500 Server Error to the user.
     * Request is not used directly in this function since we inject it as an array from controller when calling this function.
     *
     * A new function is created in this Service class to handle file upload so the code is not repeated multiple times
     * but just used when needed. It can also be created on a new Service class called FileService.
     *
     * If an exception is thrown the error is logged using Laravel logging system.
     *
     * Allcountrycourse logic is removed from here and must be placed on AllcountrycourseService to keep the logic
     * simple and cleaner.
     *
     * Slug algorithm is removed from this function and for generating course slug,  a trait should be created and then
     * used on the Model itself, so we seperate it from the other logic and also this trait can be used on other Models
     * if needed.
     * The algorithm used for generating slug must be modified since it doesn't ensure uniqueness.
     *
     * This function returns ServiceResponse object.
     *
     * @param array $data
     * @param $courseImg
     * @param $topBanner
     */
    public static function store(array $data, $courseImg, $topBanner)
    {
        try {
            DB::beginTransaction();

            if (isset($data['course_id']) && $data['course_id']) {
                AllCountryService::checkAndStore($data);
                $course = self::$courseRepository->find($data['course_id']);
            }

            // The logic below (commented lines) can be removed from the service itself and placed on the model
            // using Accessors & Mutators

            // $data['days'] = $data['days'] : 0;
            // $data['price'] = $data['price'] : 0.00;
            // $data['date1'] = $data['date1'] ? date('Y-m-d H:i:s', strtotime($data['date1']) : NULL;
            // $data['date2'] = $data['date2'] ? date('Y-m-d H:i:s', strtotime($data['date2']) : NULL;
            // $data['date3'] = $data['date3'] ? date('Y-m-d H:i:s', strtotime($data['date3']) : NULL;
            // $data['date4'] = $data['date4'] ? date('Y-m-d H:i:s', strtotime($data['date4']) : NULL;
            // $data['date5'] = $data['date5'] ? date('Y-m-d H:i:s', strtotime($data['date5']) : NULL;

            $data['course_img'] = self::uploadFile($courseImg);
            $data['top_banner'] = self::uploadFile($topBanner);

            if (isset($course) && $course !== null) {
                $course->update($data);
            } else {
                $course = self::$courseRepository->create($data);
            }

            DB::commit();

            return new ServiceResponse(true, 'Course added successfully.', $course);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('CourseService::store Exception Error: ' . $e->getMessage());

            return new ServiceResponse(false, 'Error! Course could not be added.');
        }
    }

    /**
     * Upload file and return the path of uploaded file.
     * A new service called ImageService or FileService can be created for this function.
     *
     * @param $file
     * @return string
     */
    private static function uploadFile($file)
    {
        $destinationPath = public_path().'/images/course/'; // upload path
        $filePath = $file->getClientOriginalName();
        $file->move($destinationPath, $filePath);

        return $filePath;
    }
}
