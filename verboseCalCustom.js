$jq =jQuery.noConflict();

$jq(document).ready(function() {

    $jq("#main-container").calendar({
        tipsy_gravity: 's', // How do you want to anchor the tipsy notification? (n / s / e / w)
        post_dates : ["1","2"],
        click_callback: calendar_callback, // Callback to return the clicked date
        year: "2012", // Optional, defaults to current year - pass in a year - Integer or String
        scroll_to_date: false // Scroll to the current date?
    });


    $jq(".pop_cls").on("click",function(){
        $jq("#popup_events").fadeOut("slow");
    });
});

//
// Example of callback - do something with the returned date
var calendar_callback = function(date) {

    $jq("#event_row_panel").html("");

    date.month = (date.month < 10) ? "0"+date.month : date.month;
    date.day = (date.day < 10) ? "0"+date.day : date.day;
    var activeDate = date.month+"-"+date.day+"-"+date.year;


    $jq("#popup_events_head").html("Events for "+activeDate);

    var dailyEvents = postDetailsArr[activeDate];

    var eventHTML = "";

    $jq.each(dailyEvents, function(index, data) {

        if(data.type=='event'){
            eventHTML += "<div class='event_row'><div class='event_title'><a href='"+data.guid+"' >"+data.post_title+"</a></div><div class='event_dates'>Start Date : <span>"+data.startDate+"</span>  End Date : <span>"+data.endDate+"</div></div>";
        }else{
            eventHTML += "<div class='post_row'><div class='post_title'><a href='"+data.guid+"' >"+data.post_title+"</a></div><div class='post_dates'>Published Date : <span>"+data.post_date+"</span></div></div>";
        }
			

    });

    $jq("#event_row_panel").html(eventHTML);
    $jq("#popup_events").fadeIn("slow");
//
// Returned date is a date object containing the day, month, and year.
// date.day = day; date.month = month; date.year = year;
//alert("Open your Javascript console to see the returned result object.");
//console.log(date);
}
