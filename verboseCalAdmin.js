$jq =jQuery.noConflict();

$jq(document).ready(function() {

    $jq( "#datepickerStart" ).datepicker();
    $jq( "#datepickerEnd" ).datepicker();


    $jq('#post').submit(function() {

        $jq('.ver_cal_err').remove();
        if($jq("#post_type").val() =='event'){
            var err = 0;
            if($jq("#title").val() == ''){
                $jq("#title").after("<div class='ver_cal_err'>Event Title cannot be empty</div>");
                err++;		
            }
            if($jq("#datepickerStart").val() == '' || $jq("#datepickerEnd").val() == ''){
                $jq("#datepickerEnd").after("<div class='ver_cal_err'>Start Date and End Date is required</div>");
                err++;
            }

            var start   = $jq('#datepickerStart').datepicker('getDate');
            var end = $jq('#datepickerEnd').datepicker('getDate');	
            var days   = (end - start)/1000/60/60/24;
            if(days<0){
                $jq("#datepickerEnd").after("<div class='ver_cal_err'>End Date should be greater than Start Date.</div>");
                err++;
            }
	
            if(err>0){
                return false;
            }else{
                return true;
            }
        }

                    
    });

});
