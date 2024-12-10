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



jQuery(document).ready(function($) {
	
	$('#faq-explore-button').click(function() {
        // Show all hidden FAQs
        $('.esa-faq-section .hidden').removeClass('hidden');

        // Hide the Explore All button
        $(this).hide();
    });
    
        // Check if there's a div with class "pawsitive"
        if ($('.pawsitive').length) {
            // If it exists, add the class "pawsitive-header" to the header element
            $('header').addClass('pawsitive-header');
            $('.popup-drawer').addClass('pawsitive-drawer');
        }

        // check if there's a div with class "furryfriends"

        if ($('.esa-letter-furry-friends ').length) {
            // If it exists, add the class "pawsitive-header" to the header element
            $('header').addClass('esa-furry-friends-header');
            $('.popup-drawer').addClass('esa-furry-friends-drawer');
        }
    
});


