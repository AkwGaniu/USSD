<?php
error_reporting(0);

    #We obtain the data which is contained in the post url on our server.
// http://localhost/smsRequest/result_ussd.php?MSISDN=0713038301&USSD_STRING=&serviceCode=*144#
        include "api/resultRequest_db.php";
        $text = $_GET['USSD_STRING'];
        $phonenumber = $_GET['MSISDN'];
        $serviceCode = $_GET['serviceCode'];


        $level = explode("*", $text);
        if (isset($text)) {
    

        if ( $text == "" ) {
            $response="CON \r\n Welcome to Laspotech Result Portal.\nPlease enter you matric number";
        }

        if(isset($level[0]) && $level[0]!="" && !isset($level[1])){

            $stud = "SELECT fname, department, level, phoneNum FROM students Where studentMatNum = $level[0]";

            $sql = "SELECT exam_score.courseCode, exam_score.score, courses.courseUnit FROM courses, exam_score
            WHERE exam_score.matricNum = $level[0] AND courses.courseCode = exam_score.courseCode ";


            //$courseSql = "SELECT courseUnit  FROM courses ";

            $result = mysqli_query($connect, $sql);

            $resultStud = mysqli_query($connect, $stud);
            //$courseResult = mysqli_query($connect, $courseSql);
            while($details = mysqli_fetch_assoc($resultStud)) {
                $name = $details['fname'];
                $dept = $details['department'];
                $phonenumber = $details['phoneNum'];
                $level1 = $details['level'];
            }

            if ($result) {

                if(mysqli_num_rows($result) > 0) {

                    $messageformat = "";
                    $totalCourseUnit = "";
                    $totalGP = 0;

                    while($row = mysqli_fetch_assoc($result)){

                        $cCode = $row['courseCode'];
                        // $cc = explode("", $CCode);
                        // $cCode = $cc[0].$cc[1].$cc[2]. " " . $cc[3].$cc[4].$cc[5];
                        $score = $row['score'];

                        $unit = $row['courseUnit'];

                        // Add up the course unit on each iteration 
                        $totalCourseUnit += $unit;

                        // Compute the grade point and grade on each iteration
                        if($score >= 75) {
                            $gradePoint = 4.0 * $unit;
                            $grade = 'A';  
                        } elseif ($score >= 70 and $score <= 74) {
                            $gradePoint =  3.5 * $unit;
                            $grade = 'AB';
                        } elseif ($score >= 65 and $score <= 69) {
                            $gradePoint =  3.25 * $unit;
                            $grade = 'B';
                        } elseif ($score >= 60 and $score <= 64) {
                            $gradePoint =  3.0 * $unit;
                            $grade = 'BC';
                        } elseif ($score >= 55 and $score <= 59) {
                            $gradePoint =  2.75 * $unit;
                            $grade = 'C';
                        } elseif ($score >= 50 and $score <= 54) {
                            $gradePoint =  2.5 * $unit;
                            $grade = 'CD';
                        } elseif ($score >= 45 and $score <= 49) {
                            $gradePoint =  2.25 * $unit;
                            $grade = 'D';
                        } else {
                            $gradePoint = 0 * $unit;
                            $grade = 'F';
                        }

                        $totalGP += $gradePoint;

                      //  $data[] = [$row, "grade" => $grade];
                        $messageformat .= $cCode . " => " . $score . " => " . $grade . "\r\n";

                    }

                    $GPA = $totalGP / $totalCourseUnit;

                    $GPA = round($GPA, 2);

                    $gpa = "CGPA = " . $GPA;




                    

                    $session = "2nd Semester Result \r\n2018/2019 Academic Session";
                    $matric = $level[0];

$message = "
$session
$dept
$name
$matric
$level1

$messageformat

$gpa
";


                  //  $message = $name . "\r\n" . $dept ."\r\n" . $level ."\r\n" . $messageformat;

                   $sender = "LASPOUSSD";
                   $recipient = $phonenumber;

                //    $message = urlencode($message);

                    if(send_sms_2($message,$recipient,$sender)) {

                         $response = "END \r\n You will recieve a text message containing your semester result shortly.";

                    } else {

                        $response = "END \r\n Something went wrong, please try again later.";

                    }

                } else {

                    $response = "END \r\nSorry your matric number ".$level[0].", is incorrect or has no result for this semester. \r\nPlease try again. ";
                
                } 

            }               


        }

        header('Content-type: text/plain');
        echo $response;

    }


    


function send_sms_2($message,$recipient,$sender) {
    
    $email=urlencode("lmd4sure@gmail.com"); //Note: urlencodemust be added forusername and07052726458
    $pass=urlencode("Akowanupass91"); // password as encryption code for security purpose.
    
    $message=urlencode($message);

    $live_url= "http://portal.bulksmsnigeria.net/api/?username=$email&password=$pass&message=$message&sender=$sender&mobiles=$recipient";
   
    if ($parse_url = file($live_url)) {

        return $parse_url[0];

    } else {
        
        echo "Ooops!!!: Please check your connection and try again later";
    }
    
    
}

?>
