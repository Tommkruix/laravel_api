<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TodoController extends Controller
{

    protected $user;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->user = $this->guard()->user();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Get(
     * path="/api/todos",
     * summary="Get employees data",
     * description="An authenticated user retrieving employees data using his/her ID. Add the authenticated user token in the (Auth) tab and choose (Bearer Token) type.",
     * operationId="index",
     * tags={"HR/Director Operations"},
     * @OA\Parameter(
     *         name="Bearer-Token",
     *         in="header",
     *         description="Set logged in user token",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     * @OA\Response(
     *    response=200,
     *    description="Successful operation",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Employees[]")
     *        )
     * ),
     * @OA\Response(
     *    response=422,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated.")
     *        )
     *     ),
     * )
     */
    public function index()
    {
        $todos = $this->user->todos()->get([
            'id', 
            'fullName', 
            'professionalQualification',
            'educationDegree',
            'dob',
            'phoneNumber',
            'nextOfKin',
            'maritalStatus',
            'homeAddress',
            'created_by',
            ]);
            return response()->json($todos->toArray());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Post(
     * path="/api/todos",
     * summary="Add employee data",
     * description="An authenticated user add new employee data using his/her ID",
     * operationId="store",
     * tags={"HR/Director Operations"},
     * @OA\Parameter(
     *         name="Bearer-Token",
     *         in="header",
     *         description="Set logged in user token",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"fullName","professionalQualification", "educationDegree", "dob", "phoneNumber", "nextOfKin", "maritalStatus", "homeAddress"},
     *       @OA\Property(property="fullName", type="string", format="text", example="Mr. Ajagbe Daniel Edited"),
     *       @OA\Property(property="professionalQualification", type="string", format="text", example="Professor"),
     *       @OA\Property(property="educationDegree", type="string", format="text", example="Masters"),
     *       @OA\Property(property="dob", type="string", format="text", example="1998/07/03"),
     *       @OA\Property(property="phoneNumber", type="string", format="text", example="08023456782"),
     *       @OA\Property(property="nextOfKin", type="string", format="text", example="Ajagbe Tolani"),
     *       @OA\Property(property="maritalStatus", type="string", format="text", example="Single"),
     *       @OA\Property(property="homeAddress", type="string", format="text", example="Baruwa Street"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Successful operation",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Employee{}")
     *        )
     * ),
     * @OA\Response(
     *    response=422,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Oops, the todo could not be updated.")
     *        )
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated.")
     *        )
     *     ),
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
             'fullName' => 'required|string', 
             'professionalQualification' => 'required|string',
             'educationDegree' => 'required|string',
             'dob' => 'required|string',
             'phoneNumber' => 'required|string',
             'nextOfKin' => 'required|string',
             'maritalStatus' => 'required|string',
             'homeAddress' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $todo = new Todo();
        $todo->fullName = $request->fullName;
        $todo->professionalQualification = $request->professionalQualification;
        $todo->educationDegree = $request->educationDegree;
        $todo->dob = $request->dob;
        $todo->phoneNumber = $request->phoneNumber;
        $todo->nextOfKin = $request->nextOfKin;
        $todo->maritalStatus = $request->maritalStatus;
        $todo->homeAddress = $request->homeAddress;

        if($this->user->todos()->save($todo)){
            return response()->json([
                'status' => true,
                'todo' => $todo,
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Oops, the todo could not be saved.'
            ]);
        }
    }

    /**
     * Store a newly created resource in storage using excel sheet.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function importExcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|max:5000|mimes:xlsx,xls,csv'
        ]);

        if($validator->passes()){

            $dateTime = date('Ymd_His');
            $file = $request->file('file');
            $fileName = $dateTime . '-' . $file->getClientOriginalName();
            $savePath = public_path('/upload/');
            $file->move($savePath, $fileName);

            // continue to save row by row to database

            return response()->json([
                        'status' => true,
                        'todo' => 'File uploaded successfully',
            ], 200);
        }else{
            return response()->json([
                        'status' => false,
                        'errors' => $validator->errors()->all()
                    ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function show(Todo $todo)
    {
        return $todo;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */

     /**
     * @OA\Put(
     * path="/api/todos",
     * summary="Update employee data",
     * description="An authenticated user update an employee data using his/her ID",
     * operationId="update",
     * tags={"HR/Director Operations"},
     * @OA\Parameter(
     *         name="Bearer-Token",
     *         in="header",
     *         description="Set logged in user token",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"fullName","professionalQualification", "educationDegree", "dob", "phoneNumber", "nextOfKin", "maritalStatus", "homeAddress"},
     *       @OA\Property(property="fullName", type="string", format="text", example="Mr. Ajagbe Daniel Edited"),
     *       @OA\Property(property="professionalQualification", type="string", format="text", example="Professor"),
     *       @OA\Property(property="educationDegree", type="string", format="text", example="Masters"),
     *       @OA\Property(property="dob", type="string", format="text", example="1998/07/03"),
     *       @OA\Property(property="phoneNumber", type="string", format="text", example="08023456782"),
     *       @OA\Property(property="nextOfKin", type="string", format="text", example="Ajagbe Tolani"),
     *       @OA\Property(property="maritalStatus", type="string", format="text", example="Single"),
     *       @OA\Property(property="homeAddress", type="string", format="text", example="Baruwa Street"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Successful operation",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Employee{}")
     *        )
     * ),
     * @OA\Response(
     *    response=422,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Oops, the todo could not be updated.")
     *        )
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated.")
     *        )
     *     ),
     * )
     */
    public function update(Request $request, Todo $todo)
    {
        $validator = Validator::make($request->all(), [
            'fullName' => 'required|string', 
            'professionalQualification' => 'required|string',
            'educationDegree' => 'required|string',
            'dob' => 'required|string',
            'phoneNumber' => 'required|string',
            'nextOfKin' => 'required|string',
            'maritalStatus' => 'required|string',
            'homeAddress' => 'required|string',
       ]);

       if($validator->fails()){
           return response()->json([
               'status' => false,
               'errors' => $validator->errors()
           ], 400);
       }

       $todo->fullName = $request->fullName;
        $todo->professionalQualification = $request->professionalQualification;
        $todo->educationDegree = $request->educationDegree;
        $todo->dob = $request->dob;
        $todo->phoneNumber = $request->phoneNumber;
        $todo->nextOfKin = $request->nextOfKin;
        $todo->maritalStatus = $request->maritalStatus;
        $todo->homeAddress = $request->homeAddress;

        if($this->user->todos()->save($todo)){
            return response()->json([
                'status' => true,
                'todo' => $todo,
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Oops, the todo could not be updated.'
            ]);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Todo $todo)
    {
        if($todo->delete()){
            return response()->json([
                'status' => true,
                'todo' => $todo,
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Oops, the todo could not be deleted.'
            ]);
        }
    }

    protected function guard(){
        return Auth::guard();
    }
}
