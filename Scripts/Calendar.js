function Calendar(date = null) 
{
    if (!(date instanceof Date)) 
        date = new Date();
    return {
        "date": date,
        "dayMode": function(events = []) {



        },
        "weekMode": function() {
            
        },
        "monthMode": function() {
            
        },
        "yearMode": function() {
            
        },
        "getDaysInMonth": function(year, month) {
            return new Date(year, month, 0).getDate();
        }
    };
}