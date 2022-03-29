
var facesStarted = false;

$(document).ready(function(){

    $('#faces_container .face').click(function(){
        window.location.href = $('#sign_up_path').val();
    });


    if($(window).width() >= 1020){

        //$(window).scroll(function () {
            if(!facesStarted /*&& $(this).scrollTop() > 250*/) {
                facesStarted = true;

                interval = setInterval(function () {

                    $('#faces_init_data .item .url').each(function () {
                        if (!$(this).val().length) {
                            console.log("EMPTY");
                            return;
                        }
                    });
                    clearInterval(interval);

                    FacesManager.init({
                        'initDataContainer' : $('#faces_init_data'),
                        'container' : $('#faces_container'),
                    });



                }, 500);
            }
        //});
    }

});


var FacesManager = {
    container: null,
    faces: [],
    startPreloadedIndex: null,

    init: function(settings){
        this.initDataContainer = settings.initDataContainer;
        this.container = settings.container;
        this.circlesNumber = this.container.find('.face_wrapper').length;
        this.startLoadingFrom = this.circlesNumber;
        this.createFaces();
        this.preloadFaces();
        this.animate(0, this.circlesNumber);
    },

    createFaces: function(){

        this.initDataContainer.find('.item').each(function(index){
            FacesManager.faces.push(new Face({
                'url': $(this).find('.url').val(),
                'username': $(this).find('.username').val(),
                'age': $(this).find('.age').val()
            }));

            console.log(index + " - " + FacesManager.circlesNumber);

            if(index < FacesManager.circlesNumber){

                FacesManager.container.find('.face').eq(index).attr('src', FacesManager.faces[index].url);
            }
        });

    },

    preloadFaces: function(){
        for(var i = this.startLoadingFrom; i < this.startLoadingFrom + this.circlesNumber; i++){
            if(i < this.faces.length){
                $('<img>')[0].src = this.faces[i].url;
            }
        }
    },

    needsPreloading: function(faceIndex){
         return ( (faceIndex + 1) % this.circlesNumber == 0 );
    },

    animate: function(circleIndex, faceIndex){

        if(this.needsPreloading(faceIndex)){
            this.startLoadingFrom = faceIndex + 1;
            console.log("YES: " + this.startLoadingFrom);
            this.preloadFaces();

        }

        var img = this.container.find('.face').eq(circleIndex);
        if(img.length){

            setTimeout(function(){

                if(faceIndex == FacesManager.faces.length){
                    faceIndex = 0;
                }

                img.parent().fadeOut(800);

                setTimeout(function(){
                    img.attr('src', FacesManager.faces[faceIndex].url).parent().fadeIn(1000);
                }, 900);

                FacesManager.animate(circleIndex + 1, faceIndex + 1);

            }, 4000);
        }
        else{
            this.animate(0,faceIndex);
        }
    },

};

function Face(settings){
    this.url = settings.url;
    this.username = settings.username;
    this.age = settings.age;
    this.html;
}


