console.log("Log me");

console.log("Log me");console.log("Log me");console.log("Log me");console.log("Log me");console.log("Log me");console.log("Log me");console.log("Log me");console.log("Log me");console.log("Log me");

function test() {
    alert("alerts are bad?");
}

(function() {
    console.log("Self executing function");
})();

function a() {
    var data = [];
    data.push('a');
    data.push('a');
    data.push('a');
    data.push('a');
    data.push('a');
    data.push('a');
    data.push('a');
    $.each(data, function(index, object) {
        console.log(object);
    });
}