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
    $('.esa-faq-section  .kt-acccordion-button-label-show').each(function() {
        if ($(this).hasClass('kt-accordion-panel-active')) {
            $(this).closest('.esa-faq-section  .kt-accordion-pane').addClass('accordion-pane-active');
        }
    });

    $('.esa-faq-section  .kt-acccordion-button-label-show').on('click', function() {
        $('.esa-faq-section  .kt-accordion-pane').removeClass('accordion-pane-active');
        
        if ($(this).hasClass('kt-accordion-panel-active')) {
            $(this).closest('.esa-faq-section  .kt-accordion-pane').addClass('accordion-pane-active');
        }
    });
	
	$('#faq-explore-button').click(function() {
        // Show all hidden FAQs
        $('.esa-faq-section .hidden').removeClass('hidden');

        // Hide the Explore All button
        $(this).hide();
    });
});

