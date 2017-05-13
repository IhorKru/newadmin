/**
 * Created by ihorkruchynenko on 13/05/2017.
 */
//Updating arrows and colour of the top bar
var x = document.getElementsByClassName("count_bottom");
var i;
for (i = 0; i < x.length; i++) {
    var shift = x[i].getElementsByTagName("i")[0].innerText;
    var shift = shift.replace('%',"");

    if(i != 2) {
        if(shift > 0) {
            x[i].getElementsByTagName("i")[0].className = "green";
            x[i].getElementsByTagName("i")[1].className = "fa fa-caret-up";
        } else if(shift < 0) {
            x[i].getElementsByTagName("i")[0].className = "red";
            x[i].getElementsByTagName("i")[1].className = "fa fa-caret-down";
        } else {
            x[i].getElementsByTagName("i")[0].className = "";
            x[i].getElementsByTagName("i")[1].className = "fa fa-caret-right";
        }
    } else {
        if(shift < 0) {
            x[i].getElementsByTagName("i")[0].className = "green";
            x[i].getElementsByTagName("i")[1].className = "fa fa-caret-down";
        } else if(shift < 0) {
            x[i].getElementsByTagName("i")[0].className = "red";
            x[i].getElementsByTagName("i")[1].className = "fa fa-caret-up";
        } else {
            x[i].getElementsByTagName("i")[0].className = "";
            x[i].getElementsByTagName("i")[1].className = "fa fa-caret-right";
        }
    }
}
