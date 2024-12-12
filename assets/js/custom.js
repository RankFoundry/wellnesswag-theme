// Checkout Timer
var timeInSecs;
var ticker;

function startTimer(secs) {
timeInSecs = parseInt(secs);
ticker = setInterval("tick()", 1000); 
}

function tick( ) {
var secs = timeInSecs;
if (secs > 0) {
timeInSecs--; 
}
else {
clearInterval(ticker);
startTimer(5*60); // 4 minutes in seconds
}

var mins = Math.floor(secs/60);
secs %= 60;
var pretty = ( (mins < 10) ? "0" : "" ) + mins + ":" + ( (secs < 10) ? "0" : "" ) + secs;

if(document.getElementById("countdown")){

    document.getElementById("countdown").innerHTML = pretty + ' Min';
}

}

startTimer(5*60); // 4 minutes in seconds

//Credits to Gulzaib from Pakistan


if (
  document.querySelector("#faq-explore-button") ||
  document.querySelector(".pawsitive") ||
  document.querySelector(".esa-letter-furry-friends")
) {
  // Your jQuery code here
  jQuery(document).ready(function ($) {
    // FAQ button code - only runs if element exists
    if ($("#faq-explore-button").length) {
      $("#faq-explore-button").click(function () {
        $(".esa-faq-section .hidden").removeClass("hidden");
        $(this).hide();
      });
    }

    // Pawsitive section code - only runs if element exists
    if ($(".pawsitive").length) {
      $("header").addClass("pawsitive-header");
      $(".popup-drawer").addClass("pawsitive-drawer");
    }

    // Furry friends section code - only runs if element exists
    if ($(".esa-letter-furry-friends").length) {
      $("header").addClass("esa-furry-friends-header");
      $(".popup-drawer").addClass("esa-furry-friends-drawer");
    }
  });
}
