/*******indexOf support for IE < 9 ******* */

if (!Array.prototype.indexOf) {
    Array.prototype.indexOf = function (elt /*, from*/) {
        var len = this.length >>> 0;

        var from = Number(arguments[1]) || 0;
        from = (from < 0)
            ? Math.ceil(from)
            : Math.floor(from);
        if (from < 0)
            from += len;

        for (; from < len; from++) {
            if (from in this &&
                this[from] === elt)
                return from;
        }
        return -1;
    };
}


/**********************************/


dialog = false;


$(document).ready(
    function ($) {

        /*
                $.cloudinary.config({ cloud_name: 'greendate', api_key: '333193447586872'});
                var thumbName = $('.hotitle .thumb input[type="hidden"]').val();
                if(thumbName != ""){
                    var thumbUrl = $.cloudinary.url(thumbName, { width: 32, height: 32, crop: 'thumb', gravity: 'face' })
                    $('.hotitle .thumb img').attr('src', thumbUrl);
                }
        */
        blinkingTitle = false;


        $('.delete-message-link').on('click',function(){
            let contactId = $(this).data('contactId');

            console.log(contactId);
        });

        if ($('#dialogs').size() && $(window).width() > 1020) {
            $('#dialogs').perfectScrollbar({
                wheelSpeed: 35,
                minScrollbarLength: 30
            });

            $('#dialogs').find('.ps-scrollbar-x-rail').hide();
        }

        var definition = {
            "grinning-face": {
                title: "GRINNING FACE",
                codes: ["&#128512;", "&#x1f600;"]
            },
            "smiling-eyes": {title: "GRINNING FACE WITH SMILING EYES", codes: ["&#128513;", "&#x1f601;"]},
            "tears-of-joy":
                {
                    title: "FACE WITH TEARS OF JOY",
                    codes: ["&#128514;", "&#x1f602;"]
                },
            "open-mouth": {
                title: "SMILING FACE WITH OPEN MOUTH",
                codes: ["&#128515;", "&#x1f603;"]
            },
            "smiling-eyes": {
                title: "SMILING FACE WITH OPEN MOUTH AND SMILING EYES",
                codes: ["&#128516;", "&#x1f604;"]
            },
            "cold-sweat": {
                title: "SMILING FACE WITH OPEN MOUTH AND COLD SWEAT",
                codes: ["&#x1f605;", "&#128517;"]
            },
            "tightly-cjosed-eyes": {
                title: "SMILING FACE WITH OPEN MOUTH AND TIGHTLY-CLOSED EYES",
                codes: ["&#128518;", "&#x1f606;"]
            },
            halo: {
                title: "SMILING FACE WITH HALO",
                codes: ["&#128519;", "&#x1f607;"]
            },
            horns: {
                title: "SMILING FACE WITH HORNS",
                codes: ["&#128520;", "&#x1f608;"]
            },
            winking: {
                title: "WINKING FACE",
                codes: ["&#128521;", "&#x1f609;"]
            },
            "face-smiling-eyes": {
                title: "SMILING FACE WITH SMILING EYES",
                codes: ["&#x1f60a;", "&#128522;"]
            },
            "DELICIOUS-FOOD": {
                title: "FACE SAVOURING DELICIOUS FOOD",
                codes: ["&#x1f60b;", "&#128523;"]
            },
            "RELIEVED-FACE": {
                title: "RELIEVED FACE",
                codes: ["&#128524;", "&#x1f60c;"]
            },
            "SMILING-FACE-WITH-HEART-SHAPED-EYES": {
                title: "SMILING FACE WITH HEART-SHAPED EYES",
                codes: ["&#128525;", "&#x1f60d;"]
            },
            "SMILING-FACE-WITH-SUNGLASSES": {
                title: "SMILING FACE WITH SUNGLASSES",
                codes: ["&#128526;", "&#x1f60e;"]
            },
            "SMIRKING-FACE": {
                title: "SMIRKING FACE",
                codes: ["&#128527;", "&#x1f60f;"]
            },
            "NEUTRAL-FACE": {
                title: "NEUTRAL FACE",
                codes: ["&#128528;", "&#x1f610;"]
            },
            "EXPRESSIONLESS-FACE": {
                title: "EXPRESSIONLESS FACE",
                codes: ["&#128529;", "&#x1f611;"]
            },
            "UNAMUSED-FACE": {
                title: "UNAMUSED FACE",
                codes: ["&#128530;", "&#x1f612;"]
            },
            "FACE-WITH-COLD-SWEAT": {
                title: "FACE WITH COLD SWEAT",
                codes: ["&#128531;", "&#x1f613;"]
            },
            "PENSIVE-FACE": {
                title: "PENSIVE FACE",
                codes: ["&#128532;", "&#x1f614;"]
            },
            "CONFUSED-FACE": {
                title: "CONFUSED FACE",
                codes: ["&#128533;", "&#x1f615;"]
            },
            "CONFOUNDED-FACE": {
                title: "CONFOUNDED FACE",
                codes: ["&#128534;", "&#x1f616;"]
            },
            "KISSING-FACE": {
                title: "KISSING FACE", codes: ["&#128535;", "&#x1f617;"]
            },
            "FACE-THROWING-A-KISS": {
                title: "FACE THROWING A KISS", codes: ["&#128536;", "&#x1f618;"]
            },
            "KISSING-FACE-WITH-SMILING-EYES": {
                title: "KISSING FACE WITH SMILING EYES", codes: ["&#128537;", "&#x1f619;"]
            },
            "KISSING-FACE-WITH-CLOSED-EYES": {
                title: "KISSING FACE WITH CLOSED EYES", codes: ["&#128538;", "&#x1f61a;"]
            },
            "FACE-WITH-STUCK-OUT-TONGUE": {
                title: "FACE WITH STUCK-OUT TONGUE", codes: ["&#128539;", "&#x1f61b;"]
            },
            "FACE-WITH-STUCK-OUT-TONGUE-AND-WINKING-EYE": {
                title: "FACE WITH STUCK-OUT TONGUE AND WINKING EYE", codes: ["&#128540;", "&#x1f61c;"]
            },
            "FACE-WITH-STUCK-OUT-TONGUE-AND-TIGHTLY-CLOSED-EYES": {
                title: "FACE WITH STUCK-OUT TONGUE AND TIGHTLY-CLOSED EYES",
                codes: ["&#128541;", "&#x1f61d;"]
            },
            "DISAPPOINTED-FACE": {title: "DISAPPOINTED FACE", codes: ["&#128542;", "&#x1f61e;"]},
            "WORRIED-FACE": {title: "WORRIED FACE", codes: ["&#128543;", "&#x1f61f;"]},
            "ANGRY-FACE": {title: "ANGRY FACE", codes: ["&#128544;", "&#x1f620;"]},
            "POUTING-FACE": {title: "POUTING FACE", codes: ["&#128545;", "&#x1f621;"]},
            "CRYING-FACE": {title: "CRYING FACE", codes: ["&#128546;", "&#x1f622;"]},
            "PERSEVERING-FACE": {title: "PERSEVERING FACE", codes: ["&#x1f623;", "&#128547;"]},
            "FACE-WITH-LOOK-OF-TRIUMPH": {title: "FACE WITH LOOK OF TRIUMPH", codes: ["&#128548;", "&#x1f624;"]},
            "DISAPPOINTED-BUT-RELIEVED-FACE": {
                title: "DISAPPOINTED BUT RELIEVED FACE",
                codes: ["&#128549;", "&#x1f625;"]
            },
            "FROWNING-FACE-WITH-OPEN-MOUTH": {
                title: "FROWNING FACE WITH OPEN MOUTH",
                codes: ["&#128550;", "&#x1f626;"]
            },
            "ANGUISHED-FACE": {title: "ANGUISHED FACE", codes: ["&#128551;", "&#x1f627;"]},
            "FEARFUL-FACE": {title: "FEARFUL FACE", codes: ["&#128552;", "&#x1f628;"]},
            "WEARY-FACE": {title: "WEARY FACE", codes: ["&#128553;", "&#x1f629;"]},
            "SLEEPY-FACE": {title: "SLEEPY FACE", codes: ["&#128554;", "&#x1f62a;"]},
            "TIRED-FACE": {title: "TIRED FACE", codes: ["&#128555;", "&#x1f62b;"]},
            "GRIMACING-FACE": {title: "GRIMACING FACE", codes: ["&#128556;", "&#x1f62c;"]},
            "LOUDLY-CRYING-FACE": {title: "LOUDLY CRYING FACE", codes: ["&#128557;", "&#x1f62d;"]},
            "FACE-WITH-OPEN-MOUTH": {title: "FACE WITH OPEN MOUTH", codes: ["&#128558;", "&#x1f62e;"]},
            "HUSHED-FACE": {title: "HUSHED FACE", codes: ["&#128559;", "&#x1f62f;"]},
            "FACE-WITH-OPEN-MOUTH-AND-COLD-SWEAT": {
                title: "FACE WITH OPEN MOUTH AND COLD SWEAT",
                codes: ["&#128560;", "&#x1f630;"]
            },
            "FACE-SCREAMING-IN-FEAR": {title: "FACE SCREAMING IN FEAR", codes: ["&#128561;", "&#x1f631;"]},
            "ASTONISHED-FACE": {title: "ASTONISHED FACE", codes: ["&#128562;", "&#x1f632;"]},
            "FLUSHED-FACE": {title: "FLUSHED FACE", codes: ["&#128563;", "&#x1f633;"]},
            "SLEEPING-FACE": {title: "SLEEPING FACE", codes: ["&#128564;", "&#x1f634;"]},
            "DIZZY-FACE": {title: "DIZZY FACE", codes: ["&#128565;", "&#x1f635;"]},
            "FACE-WITHOUT-MOUTH": {title: "FACE WITHOUT MOUTH", codes: ["&#128566;", "&#x1f636;"]},
            "FACE-WITH-MEDICAL-MASK": {title: "FACE WITH MEDICAL MASK", codes: ["&#128567;", "&#x1f637;"]},
            "SMILING-CAT-FACE-WITH-OPEN-MOUTH": {
                title: "SMILING CAT FACE WITH OPEN MOUTH",
                codes: ["&#128570;", "&#x1f63a;"]
            },
            "SMILING-CAT-FACE-WITH-HEART-SHAPED-EYES": {
                title: "SMILING CAT FACE WITH HEART-SHAPED EYES",
                codes: ["&#128571;", "&#x1f63b;"]
            },
            "FACE-WITH-NO-GOOD-GESTURE": {title: "FACE WITH NO GOOD GESTURE", codes: ["&#128581;", "&#x1f645;"]},
            "FACE-WITH-OK-GESTURE": {title: "FACE WITH OK GESTURE", codes: ["&#128582;", "&#x1f646;"]},
            "PERSON-BOWING-DEEPLY": {title: "PERSON BOWING DEEPLY", codes: ["&#128583;", "&#x1f647;"]},
            "HAPPY-PERSON-RAISING-ONE-HAND": {
                title: "HAPPY PERSON RAISING ONE HAND",
                codes: ["&#128587;", "&#x1f64b;"]
            },
            "PERSON-RAISING-BOTH-HANDS-IN-CELEBRATION": {
                title: "PERSON RAISING BOTH HANDS IN CELEBRATION",
                codes: ["&#128588;", "&#x1f64c;"]
            },
            "PERSON-FROWNING": {title: "PERSON FROWNING", codes: ["&#128589;", "&#x1f64d;"]},
            "PERSON-WITH-POUTING-FACE": {title: "PERSON WITH POUTING FACE", codes: ["&#128590;", "&#x1f64e;"]},
            "PERSON-WITH-FOLDED-HANDS": {title: "PERSON WITH FOLDED HANDS", codes: ["&#128591;", "&#x1f64f;"]},
            sun: {title: "Sun", codes: ["&#9728;", "&#x2600;"]},
            cloud: {title: "Cloud", codes: ["&#9729;", "&#x2601;"]},
            umbrella: {title: "Umbrella", codes: ["&#9730;", "&#x2602;"]},
            snowman: {title: "Snowman", codes: ["&#9731;", "&#x2603;"]},
            meteorite: {title: "Meteorite", codes: ["&#9732;", "&#x2604;"]},
            tel: {title: "Tel", codes: ["&#9742;", "&#x260e;"]},
            phone: {title: "Phone", codes: ["&#9743;", "&#x260f;"]},
            "Umbrella-with-rain": {title: "Umbrella with rain", codes: ["&#9748;", "&#x2614;"]},
            "Cup-of-coffee": {title: "Cup of coffee", codes: ["&#9749;", "&#x2615;"]},
            "Green-clever": {title: "Green clever", codes: ["&#9752;", "&#x2618;"]},
            "hand-up": {title: "Hand up", codes: ["&#9757;", "&#x261d;"]},
            music: {title: "Music", codes: ["&#9835;", "&#x266b;"]},
            checked: {title: "Checked", codes: ["&#9989;", "&#x2705;"]},
            plane: {title: "Plane", codes: ["&#9992;", "&#x2708;"]},
            "Ok-Doky": {title: "Ok Doky", codes: ["&#9996;", "&#x270c;"]},
            pencil: {title: "Pencil", codes: ["&#9998;", "&#x270e;"]},
            cross: {title: "Cross", codes: ["&#10014;", "&#x271e;"]},
            "star-of-david": {title: "Star of David", codes: ["&#10017;", "&#x2721;"]},
            "CYCLONE-typhoon-hurricane": {title: "CYCLONE = typhoon, hurricane", codes: ["&#127744;", "&#x1f300;"]},
            foggy: {title: "FOGGY", codes: ["&#127745;", "&#x1f301;"]},
            "CLOSED UMBRELLA": {title: "CLOSED UMBRELLA", codes: ["&#127746;", "&#x1f302;"]},
            "NIGHT-WITH-STARS": {title: "NIGHT WITH STARS", codes: ["&#127747;", "&#x1f303;"]},
            "SUNRISE-OVER-MOUNTAINS": {title: "SUNRISE OVER MOUNTAINS", codes: ["&#127748;", "&#x1f304;"]},
            "SUNRISE": {title: "SUNRISE", codes: ["&#127749;", "&#x1f305;"]},
            "RAINBOW": {title: "RAINBOW", codes: ["&#127752;", "&#x1f308;"]},
            "BRIDGE-AT-NIGHT": {title: "BRIDGE AT NIGHT", codes: ["&#127753;", "&#x1f309;"]},
            "WATER-WAVE": {title: "WATER WAVE", codes: ["&#127754;", "&#x1f30a;"]},
            "VOLCANO": {title: "VOLCANO", codes: ["&#127755;", "&#x1f30b;"]},
            "MILKY-WAY": {title: "MILKY WAY", codes: ["&#127756;", "&#x1f30c;"]},
            "EARTH-GLOBE-AMERICAS": {title: "EARTH GLOBE AMERICAS", codes: ["&#x1f30e;", "&#127758;"]},
            "CRESCENT-MOON": {title: "CRESCENT MOON", codes: ["&#127769;", "&#x1f319;"]},
            "SUN-WITH-FACE": {title: "SUN WITH FACE", codes: ["&#127774;", "&#x1f31e;"]},
            "SHOOTING-STAR": {title: "SHOOTING STAR", codes: ["&#127776;", "&#x1f320;"]},
            "CHESTNUT": {title: "CHESTNUT", codes: ["&#127792;", "&#x1f330;"]},
            "SEEDLING": {title: "SEEDLING", codes: ["&#127793;", "&#x1f331;"]},
            "EVERGREEN-TREE": {title: "EVERGREEN TREE", codes: ["&#127794;", "&#x1f332;"]},
            "DECIDUOUS-TREE": {title: "DECIDUOUS TREE", codes: ["&#127795;", "&#x1f333;"]},
            "PALM-TREE": {title: "PALM TREE", codes: ["&#127796;", "&#x1f334;"]},
            "CACTUS": {title: "CACTUS", codes: ["&#127797;", "&#x1f335;"]},
            "Reserved-Not-Used": {title: "Reserved / Not Used", codes: ["&#127798;", "&#x1f336;"]},
            "TULIP": {title: "TULIP", codes: ["&#127799;", "&#x1f337;"]},
            "CHERRY-BLOSSOM": {title: "CHERRY BLOSSOM", codes: ["&#127800;", "&#x1f338;"]},
            "ROSE": {title: "ROSE", codes: ["&#127801;", "&#x1f339;"]},
            "HIBISCUS": {title: "HIBISCUS", codes: ["&#127802;", "&#x1f33a;"]},
            "SUNFLOWER": {title: "SUNFLOWER", codes: ["&#127803;", "&#x1f33b;"]},
            "EAR-OF-MAIZE": {title: "EAR OF MAIZE", codes: ["&#127805;", "&#x1f33d;"]},
            "EAR-OF-RICE": {title: "EAR OF RICE", codes: ["&#127806;", "&#x1f33e;"]},
            "FOUR-LEAF-CLOVER": {title: "FOUR LEAF CLOVER", codes: ["&#127808;", "&#x1f340;"]},
            "MAPLE-LEAF": {title: "MAPLE LEAF", codes: ["&#127809;", "&#x1f341;"]},
            "FALLEN-LEAF": {title: "FALLEN LEAF", codes: ["&#127810;", "&#x1f342;"]},
            "MUSHROOM": {title: "MUSHROOM", codes: ["&#127812;", "&#x1f344;"]},
            "TOMATO": {title: "TOMATO", codes: ["&#127813;", "&#x1f345;"]},
            "AUBERGINE-eggplant": {title: "AUBERGINE = eggplant", codes: ["&#127814;", "&#x1f346;"]},
            "GRAPES": {title: "GRAPES", codes: ["&#127815;", "&#x1f347;"]},
            "MELON": {title: "MELON", codes: ["&#127816;", "&#x1f348;"]},
            "WATERMELON": {title: "WATERMELON", codes: ["&#127817;", "&#x1f349;"]},
            "TANGERINE": {title: "TANGERINE", codes: ["&#127818;", "&#x1f34a;"]},
            "LEMON": {title: "LEMON", codes: ["&#127819;", "&#x1f34b;"]},
            "BANANA": {title: "BANANA", codes: ["&#127820;", "&#x1f34c;"]},
            "PINEAPPLE": {title: "PINEAPPLE", codes: ["&#127821;", "&#x1f34d;"]},
            "RED-APPLE": {title: "RED APPLE", codes: ["&#127822;", "&#x1f34e;"]},
            "GREEN-APPLE": {title: "GREEN APPLE", codes: ["&#127823;", "&#x1f34f;"]},
            "HAMBURGER": {title: "HAMBURGER", codes: ["&#127828;", "&#x1f354;"]},
            "SLICE-OF-PIZZA": {title: "SLICE OF PIZZA", codes: ["&#127829;", "&#x1f355;"]},
            "POULTRY-LEG": {title: "POULTRY LEG", codes: ["&#127831;", "&#x1f357;"]},
            "SPAGHETTI": {title: "SPAGHETTI", codes: ["&#127837;", "&#x1f35d;"]},
            "BREAD": {title: "BREAD", codes: ["&#127838;", "&#x1f35e;"]},
            "FRENCH-FRIES": {title: "FRENCH FRIES", codes: ["&#127839;", "&#x1f35f;"]},
            "SOFT-ICE-CREAM": {title: "SOFT ICE CREAM", codes: ["&#127846;", "&#x1f366;"]},
            "ICE-CREAM": {title: "ICE CREAM", codes: ["&#127848;", "&#x1f368;"]},
            "DOUGHNUT": {title: "DOUGHNUT", codes: ["&#127849;", "&#x1f369;"]},
            "CHOCOLATE-BAR": {title: "CHOCOLATE BAR", codes: ["&#127851;", "&#x1f36b;"]},
            "HONEY-POT": {title: "HONEY POT", codes: ["&#127855;", "&#x1f36f;"]},
            "SHORTCAKE": {title: "SHORTCAKE", codes: ["&#127856;", "&#x1f370;"]},
            "COOKING": {title: "COOKING", codes: ["&#127859;", "&#x1f373;"]},
            "WINE-GLASS": {title: "WINE GLASS", codes: ["&#127863;", "&#x1f377;"]},
            "COCKTAIL-GLASS": {title: "COCKTAIL GLASS", codes: ["&#127864;", "&#x1f378;"]},
            "TROPICAL-DRINK": {title: "TROPICAL DRINK", codes: ["&#127865;", "&#x1f379;"]},
            "BEER-MUG": {title: "BEER MUG", codes: ["&#127866;", "&#x1f37a;"]},
            "WRAPPED-PRESENT": {title: "WRAPPED PRESENT", codes: ["&#127873;", "&#x1f381;"]},
            "BIRTHDAY-CAKE": {title: "BIRTHDAY CAKE", codes: ["&#127874;", "&#x1f382;"]},
            "JACK-O-LANTERN": {title: "JACK-O-LANTERN", codes: ["&#127875;", "&#x1f383;"]},
            "CHRISTMAS-TREE": {title: "CHRISTMAS TREE", codes: ["&#127876;", "&#x1f384;"]},
            "FATHER-CHRISTMAS": {title: "FATHER CHRISTMAS", codes: ["&#127877;", "&#x1f385;"]},
            "BALLOON": {title: "BALLOON", codes: ["&#127880;", "&#x1f388;"]},
            "PARTY-POPPER": {title: "PARTY POPPER", codes: ["&#127881;", "&#x1f389;"]},
            "CONFETTI-BALL": {title: "CONFETTI BALL", codes: ["&#127882;", "&#x1f38a;"]},
            "ROLLER-COASTER": {title: "ROLLER COASTER", codes: ["&#127906;", "&#x1f3a2;"]},
            "MOVIE-CAMERA": {title: "MOVIE CAMERA", codes: ["&#127909;", "&#x1f3a5;"]},
            "FISHING-POLE-AND-FISH": {title: "FISHING POLE AND FISH", codes: ["&#127907;", "&#x1f3a3;"]},
            "ARTIST-PALETTE": {title: "ARTIST PALETTE", codes: ["&#127912;", "&#x1f3a8;"]},
            "CIRCUS-TENT": {title: "CIRCUS TENT", codes: ["&#127914;", "&#x1f3aa;"]},
            "SLOT-MACHINE": {title: "SLOT MACHINE", codes: ["&#127920;", "&#x1f3b0;"]},
            "DIRECT-HIT": {title: "DIRECT HIT", codes: ["&#127919;", "&#x1f3af;"]},
            "MUSICAL-NOTE": {title: "MUSICAL NOTE", codes: ["&#127925;", "&#x1f3b5;"]},
            "VIOLIN": {title: "VIOLIN", codes: ["&#127931;", "&#x1f3bb;"]},
            "BASKETBALL-AND-HOOP": {title: "BASKETBALL AND HOOP", codes: ["&#127936;", "&#x1f3c0;"]},
            "CHEQUERED-FLAG": {title: "CHEQUERED FLAG", codes: ["&#127937;", "&#x1f3c1;"]},
            "HORSE-RACING": {title: "HORSE RACING", codes: ["&#127943;", "&#x1f3c7;"]},
            "AMERICAN-FOOTBALL": {title: "AMERICAN FOOTBALL", codes: ["&#127944;", "&#x1f3c8;"]},
            "HOUSE-WITH-GARDEN": {title: "HOUSE WITH GARDEN", codes: ["&#127969;", "&#x1f3e1;"]},
            "FAMILY": {title: "FAMILY", codes: ["&#128106;", "&#x1f46a;"]},
            "MAN-AND-WOMAN-HOLDING-HANDS": {title: "MAN AND WOMAN HOLDING HANDS", codes: ["&#128107;", "&#x1f46b;"]},
            "BRIDE-WITH-VEIL": {title: "BRIDE WITH VEIL", codes: ["&#128112;", "&#x1f470;"]},
            "DANCER": {title: "DANCER", codes: ["&#128131;", "&#x1f483;"]},
            "KISS": {title: "KISS", codes: ["&#128143;", "&#x1f48f;"]},
            "BOUQUET": {title: "BOUQUET", codes: ["&#128144;", "&#x1f490;"]},
            "COUPLE-WITH-HEART": {title: "COUPLE WITH HEART", codes: ["&#128145;", "&#x1f491;"]},
            "WEDDING": {title: "WEDDING", codes: ["&#128146;", "&#x1f492;"]},
            "SPARKLING-HEART": {title: "SPARKLING HEART", codes: ["&#128150;", "&#x1f496;"]},
            "HEART-WITH-RIBBON": {title: "HEART WITH RIBBON", codes: ["&#128157;", "&#x1f49d;"]},
            "BOMB": {title: "BOMB", codes: ["&#128163;", "&#x1f4a3;"]},
            "MONEY-BAG": {title: "MONEY BAG", codes: ["&#128176;", "&#x1f4b0;"]},
            "BANKNOTE-WITH-DOLLAR-SIGN": {title: "BANKNOTE WITH DOLLAR SIGN", codes: ["&#128181;", "&#x1f4b5;"]},
            "SEAT": {title: "SEAT", codes: ["&#128186;", "&#x1f4ba;"]},
            "ROUND-PUSHPIN": {title: "ROUND PUSHPIN", codes: ["&#128205;", "&#x1f4cd;"]},
            "PAPERCLIP": {title: "PAPERCLIP", codes: ["&#128206;", "&#x1f4ce;"]},
            "PUBLIC-ADDRESS-LOUDSPEAKER": {title: "PUBLIC ADDRESS LOUDSPEAKER", codes: ["&#128226;", "&#x1f4e2;"]},
            "INCOMING-ENVELOPE": {title: "INCOMING ENVELOPE", codes: ["&#128232;", "&#x1f4e8;"]},
            "POSTBOX": {title: "POSTBOX", codes: ["&#x1f4ee;", "&#128238;"]},
            "NEWSPAPER": {title: "NEWSPAPER", codes: ["&#x1f4f0;", "&#128240;"]},
            "MOBILE-PHONE": {title: "MOBILE PHONE", codes: ["&#128241;", "&#x1f4f1;"]},
            "CLOSED-LOCK-WITH-KEY": {title: "CLOSED LOCK WITH KEY", codes: ["&#128272;", "&#x1f510;"]},
            "KEY": {title: "KEY", codes: ["&#128273;", "&#x1f511;"]},
            "LOCK": {title: "LOCK", codes: ["&#x1f512;", "&#128274;"]},
            "STATUE-OF-LIBERTY": {title: "STATUE OF LIBERTY", codes: ["&#128509;", "&#x1f5fd;"]},
        };
        $.emoticons.define(definition);

        if ($('.mid_content.hadaot_mid').size()) {
            $('.mid_content.hadaot_mid .nf_div').html($.emoticons.replace($('.mid_content.hadaot_mid .nf_div').html()));
        }


        if ($('#dialog, #dialog_mob').size()) {

            $('.emoticons_wrapper').html($.emoticons.toString());

            // $('#dialog').html($.emoticons.replace($('#dialog').html()));

            // $('#dialog_mob').html($.emoticons.replace($('#dialog_mob').html()));
        }

        $('#dialog_textarea1').click(function () {
            //doSave();
        });
        $('#dialog_textarea1').keyup(function () {
            //alert(1);
            //savedSelection = saveSelection( document.getElementById('dialog_textarea1') );
            //alert(savedSelection.start);

            var text = $(this).html().replace(/<(|\/)div>/g, function (match) {
                //savedSelection.end = savedSelection.start += 1;
                return match == '<div>' ? '<br>' : '';

            });
            //$(this).html(text);
            //restoreSelection(document.getElementById('dialog_textarea1'), savedSelection);
            text = text.replace(/<br>/g, '\r\n').replace(/<\/?[^>]+(>|$)/g, "");
            //alert();
            $('#dialog_textarea').val(text);
        });

        /*$('#dialog_textarea1').html(function(i, html) {
            return html.replace(/<(|\/)div>/g, function(match) {
                alert(match);
                return match == '<div>' ? '<br>' : '';
            });
        });*/


        if ($('#dialog, #dialog_mob').size() && $(window).width() > 625) {


            Messenger.qtipInit($('#show_emoticons'), $('#emoticonsTemplate'), Messenger.emoticonsBindClickEvent, 'Insert emoticon');

            /*
            $('#dialog, #dialog_mob').perfectScrollbar({
                wheelSpeed: 35,
                minScrollbarLength: 30
            });

            $("#dialog, #dialog_mob").scrollTop( $( "#dialog" ).prop( "scrollHeight" ) );
            $("#dialog, #dialog_mob").perfectScrollbar('update');
            */

            $('#dialog_textarea').keypress(function (e) {
                if (dialog.enterKeyPressed(e)) {
                    dialog.sendMessage($(this));
                }
            });

            //$('#dialog').scrollTop(($(this).height() * 2) + $(document).height());
            $('#dialog').scrollTop($("#dialog .msg_text").last().offset().top + $("#dialog .msg_text").last().height() + 300);
            $('.nmobile_footer .send_message_area').html('');
            $('html, body').scrollTop($(".msg_but").offset().top - $(window).height() + 40);

        }

        if ($('#dialog, #dialog_mob').size() && $(window).width() < 625) {
            //$('.msg_body .send_message_area').find('div#dialog_textarea1').addClass('hidden');
            //$('.msg_body .send_message_area').find('textarea').removeClass('hidden');
            $('.nmobile_footer .send_message_area').html($('.msg_body .send_message_area').html()).css({'display': 'inline-block'});
            $('.msg_body .send_message_area').html('');
            $('#dialog_mob').html($('#dialog').html());
            $('html,body').height($('.nymain_mobile').outerHeight());
            //mid_content
            //$('.nymain_mobile .msg_text').last().offset().top + $(".nymain_mobile .msg_text").last().height() + 300
            //alert($('.nymain_mobile .msg_text').last().offset().top + $(".nymain_mobile .msg_text").last().height() + 300);
            //$('footer, .greybox2.ad').hide();
            //$('textarea').autogrow({vertical: true, horizontal: false});
            $('html, body').scrollTop($(document).height());

            //$('#dialog_mob').scrollTop($("#dialog_mob .msg_text").last().offset().top + $("#dialog_mob .msg_text").last().height() + 300);
        }


    }
);

$(window).resize(function () {
//window.onresize = function() {
    if ($('#dialog').size() && $(window).width() > 625 && $('#dialog_mob').html().trim() != '') {

        //
        if ($('.nmobile_footer .send_message_area').html() != '' && $('.msg_body .send_message_area').html() == '') {
            //$('.msg_body .send_message_area').find('div#dialog_textarea1').removeClass('hidden');
            //$('.msg_body .send_message_area').find('textarea').addClass('hidden');
            $('.msg_body .send_message_area').html($('.nmobile_footer .send_message_area').html());
            $('.nmobile_footer .send_message_area').html('').removeAttr('style');
            $('#dialog').html($('#dialog_mob').html());
        }

        $('#dialog').scrollTop($("#dialog .msg_text").last().offset().top + $("#dialog .msg_text").last().height() + 300);
        if ($(".msg_body .msg_but").size() > 0) {
            $('html, body').scrollTop($(".msg_body .msg_but").offset().top - $(window).height() + 40);
        }

    }
    if ($('#dialog_mob').size() && $(window).width() < 625 && $('#dialog').html().trim() != '') {

        if ($('.msg_body .send_message_area').html() != '' && $('.nmobile_footer .send_message_area').html() == '') {
            //$('.msg_body .send_message_area').find('div#dialog_textarea1').addClass('hidden');
            //$('.msg_body .send_message_area').find('textarea').removeClass('hidden');
            $('.nmobile_footer .send_message_area').html($('.msg_body .send_message_area').html()).css({'display': 'inline-block'});
            $('.msg_body .send_message_area').html('');
            $('#dialog_mob').html($('#dialog').html());
        }
        $('html,body').height($('.nymain_mobile').outerHeight());
        $('html, body').scrollTop($(document).height());
        //$('#dialog_mob').scrollTop($("#dialog_mob .msg_text").last().offset().top + $("#dialog_mob .msg_text").last().height() + 300);
    }
});


window.onload = function () {
    Messenger.init();

    dialog = false;

    var contactId = $('#contactId').val();


    var isMobile = $(window).width() < 625;

    var options = {
        contactId: contactId,
        contactName: $('#contactNickname').val(),
        userName: $('#userNickname').val(),
        creatingInCycle: false,
        isMobile: isMobile
    };

    Dialog.prototype = new Chat(options);
    Dialog.prototype.constructor = Dialog;
    dialog = new Dialog(options);
    if (contactId) {

        $('#dialog .unreadMessage').each(function () {
            var message = {
                id: $(this).val(),
                from: $('#curentUserId').val(),
                text: "",
                userImage: $('#curentUserImage').val(),
                dateTime: "",
                isSaved: true,
            };
            Messenger.addUnreadMessageId(message);
        });

        Messenger.checkActiveWindowsNewMessages();


    }

};

var saveSelection, restoreSelection;

if (window.getSelection && document.createRange) {
    saveSelection = function (containerEl) {
        var range = window.getSelection().getRangeAt(0);
        var preSelectionRange = range.cloneRange();
        preSelectionRange.selectNodeContents(containerEl);
        preSelectionRange.setEnd(range.startContainer, range.startOffset);
        var start = preSelectionRange.toString().length;
        //alert(start);
        return {
            start: start,
            end: start + range.toString().length
        }
    };

    restoreSelection = function (containerEl, savedSel) {
        var charIndex = 0, range = document.createRange();
        range.setStart(containerEl, 0);
        range.collapse(true);
        var nodeStack = [containerEl], node, foundStart = false, stop = false;

        while (!stop && (node = nodeStack.pop())) {
            if (node.nodeType == 3) {
                var nextCharIndex = charIndex + node.length;
                if (!foundStart && savedSel.start >= charIndex && savedSel.start <= nextCharIndex) {
                    range.setStart(node, savedSel.start - charIndex);
                    foundStart = true;
                }
                if (foundStart && savedSel.end >= charIndex && savedSel.end <= nextCharIndex) {
                    range.setEnd(node, savedSel.end - charIndex);
                    stop = true;
                }
                charIndex = nextCharIndex;
            } else {
                var i = node.childNodes.length;
                while (i--) {
                    nodeStack.push(node.childNodes[i]);
                }
            }
        }

        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    }
} else if (document.selection && document.body.createTextRange) {
    saveSelection = function (containerEl) {
        var selectedTextRange = document.selection.createRange();
        var preSelectionTextRange = document.body.createTextRange();
        preSelectionTextRange.moveToElementText(containerEl);
        preSelectionTextRange.setEndPoint("EndToStart", selectedTextRange);
        var start = preSelectionTextRange.text.length;

        return {
            start: start,
            end: start + selectedTextRange.text.length
        }
    };

    restoreSelection = function (containerEl, savedSel) {
        var textRange = document.body.createTextRange();
        textRange.moveToElementText(containerEl);
        textRange.collapse(true);
        textRange.moveEnd("character", savedSel.end);
        textRange.moveStart("character", savedSel.start);
        textRange.select();
    };
}

var savedSelection;

function doSave() {
    savedSelection = saveSelection(document.getElementById("dialog_textarea1"));
    //doRestore();
}

function doRestore() {
    if (savedSelection) {
        restoreSelection(document.getElementById("dialog_textarea1"), savedSelection);
    }
}

function Dialog(options) {

    this.sentMessagesArea = $('#dialog');
    this.username = options.userName;
    this.newMessagesRequest = '';
    this.isMobile = options.isMobile;

    //Chat.apply(this, options);
    this.insertMessage = function (message) {
        this.processMessage(message);
        var html = Messenger.dialogMessageTemplate.replace(/\[MESSAGE\]/g, message.text);
        var direction = (message.from == this.contactId) ? "" : "msg_text2";
        var status = this.getMessageStatus(message);
        html = html.replace(/\[STATUS\]/, status);
        html = html.replace(/\[DIRECTION\]/, direction);
        html = html.replace(/\[DATE_TIME\]/, message.dateTime);
        html = html.replace(/\[MESSAGE_SECTION_ID\]/g, message.id);

        html = $.emoticons.replace(html);

        if ($(window).width() < 625) {
            $('#dialog_mob #scroll_end').before(html);
            $('html,body').height($('.nymain_mobile').outerHeight());
            $('html, body').scrollTop($(document).height());
        } else {
            $('#dialog #scroll_end').before(html);
            $('#dialog').scrollTop($("#dialog #scroll_end").offset().top);
            setTimeout(function () {
                setTimeout(function () {
                    $('#dialog').scrollTop($("#dialog #scroll_end").offset().top + 3000)
                }, 1);
            }, 1);
            $('html, body').scrollTop($(".msg_but").offset().top - $(window).height() + 40);
        }
    };

    this.needToCloneMessage = function (message) {
        var chat = Messenger.getChat(this.contactId);
        if (chat) {
            return true;
        }

        return false;
    };

    this.cloneMessage = function (message) {
        var chat = Messenger.getChat(this.contactId);
        chat.insertMessage(message);
    };

    this.updateMessageSection = function (sectionId, message) {

        $('#dialogMessageSection_' + sectionId)
            .addClass('dialogMessageSection_' + message.id)
            .addClass(Messenger.messageStatus.saved)
            .find('.mdate')
            .text(message.dateTime)
        ;
        $('#dialogMessageSection_' + sectionId).next('.unreadMessage').val(message.id);
        $('#dialogMessageSection_' + sectionId).find('.trash').attr('data-id', message.id);
        if (this.needToCloneMessage(message)) {
            $('#chatMessageSection_' + sectionId).addClass('chatMessageSection_' + message.id);
            $('#chatMessageSection_' + sectionId).find('.dateTime').text(message.dateTime);
            $('#chatMessageSection_' + sectionId).find('.status').html(Messenger.messageStatus.saved);
            $('#chatMessageSection_' + sectionId).find('.userPicture').attr('src', message.senderImage);
        }

    };

    this.enterKeyPressed = function (e) {
        return false;
    };

}


function Chat(options) {
    this.contactId = options.contactId;
    this.contactName = options.contactName;
    this.creatingInCycle = options.creatingInCycle;
    this.chatWraper;
    this.scrollBarWraper;
    this.sentMessagesArea;
    this.isMinimized;
    this.waitingMessages = [];
    this.allowedToReadMessage;
    this.isMobile = false;

    this.getContactId = function () {
        return this.contactId;
    };

    this.getContactName = function () {
        return this.contactName;
    };

    this.open = function () {


        var chat = Messenger.getChat(this.contactId);
        if (!chat) {
            Messenger.setChat(this);
            $.ajax({
                url: '/messenger/chat/open/' +
                    '' +
                    'Id:' + Messenger.currentUserId + '/contactId:' + this.contactId,
                headers: {'apiKey': Messenger.apiKey},
                timeout: 80000,
                dataType: 'json',
                context: this,
                error: function (error) {
                },
                success: function (response, status) {

                    var html = Messenger.template.html.replace(/\[CONTACT_ID\]/g, this.contactId);
                    html = html.replace(/\[CONTACT_NAME\]/g, this.contactName);

                    var activeChatsNumber = Messenger.getAllChats().length;

                    var thisChat = this;

                    this.chatWraper = $('.chatsArea').append(html).find('.chatWindow:last-child');

                    this.setOverAll();

                    this.chatWraper
                        .click(function () {
                            thisChat.setOverAll();
                        })
                        .find('.close')
                        .click(function () {
                            thisChat.close();
                        })
                        .siblings('.minimize, .header')
                        .click(function () {
                            thisChat.minimize();
                        });

                    this.chatWraper.find('textarea').keypress(function (e) {
                        if (thisChat.enterKeyPressed(e)) {
                            thisChat.sendMessage($(this));
                        }
                    });

                    this.scrollBarWraper = this.chatWraper.find('.scrollbar1');
                    this.sentMessagesArea = this.scrollBarWraper.find('.overview');

                    this.sentMessagesArea.initHeight = 245;

                    this.sentMessagesArea.needsToBeScrolled = function () {
                        return (this.height() > this.initHeight) ? true : false;
                    };

                    this.sentMessagesArea.scroll = function () {
                        var height = this.height();
                        var initHeight = this.initHeight;
                    };

                    if (this.creatingInCycle)
                        this.minimize();

                    var isNew = false;

                    Messenger.currentUserHasPoints = response.currentUserHasPoints;

                    if (response.chatHistory.length > 0) {
                        for (var i in response.chatHistory) {
                            var message = response.chatHistory[i];
                            this.insertMessage(message);
                            //alert(message.isRead);
                            if (message.from == this.contactId && !message.isRead) {
                                isNew = true;
                                if (!this.isMinimized && this.allowedToReadMessage)
                                    this.setMessageAsRead(message);
                                else
                                    this.addMessageToWaiting(message);
                            }
                        }
                    }

                    if (isNew && this.isMinimized) {
                        this.blinkingStart();
                        Messenger.blinkingTitleStart();
                        $('body').append('<embed src="/assets/frontend/media/newMessage.mp3" autoplay="true" autostart="true" type="audio/x-wav" width="1" height="1">');
                    }

                    if (this.sentMessagesArea.needsToBeScrolled()) {
                        //this.sentMessagesArea.scroll();
                    }

                    if (!this.creatingInCycle)
                        Messenger.checkActiveWindowsNewMessages();
                    else
                        setTimeout(function () {
                            Messenger.checkActiveWindowsNewMessages();
                        }, 200);

                    Messenger.relocateChats();
                }

            });

        } else {
            if (!this.creatingInCycle)
                Messenger.checkActiveWindowsNewMessages();
            else
                setTimeout(function () {
                    Messenger.checkActiveWindowsNewMessages();
                }, 200);
        }
    };


    this.close = function () {
        $.ajax({
            url: '/messenger/chat/close/userId:' + Messenger.currentUserId + '/contactId:' + this.contactId,
            headers: {'apiKey': Messenger.apiKey},
            error: function (error) {
            },
            timeout: 80000,
            dataType: 'json',
            context: this,
            success: function (response, status) {
                if (response.success) {
                    $('.chatsArea').find('#' + this.contactId).remove();
                    Messenger.unsetChat(this);
                    Messenger.relocateChats();
                    Messenger.blinkingTitleStop();
                }
            }
        });

    };

    this.minimize = function () {
        $('#' + this.contactId).find('.body, textarea').toggleClass('hiden');
        this.isMinimized = $('#' + this.contactId).find('.body, textarea').hasClass('hiden');

        if (!this.isMinimized) {

            if (this.allowedToReadMessage && this.waitingMessages.length > 0) {
                for (var i in this.waitingMessages) {
                    var message = this.waitingMessages[i];
                    this.setMessageAsRead(message);
                }
            }

            this.waitingMessages = [];

            if (this.sentMessagesArea.needsToBeScrolled()) {
                //this.sentMessagesArea.scroll();
            }

            this.blinkingStop();
            Messenger.blinkingTitleStop();

            this.setOverAll();

        }

    };

    this.setOverAll = function () {
        $('.chatWindow').css({"z-index": 10});
        $('#' + this.contactId).css({"z-index": 100});
    };

    this.enterKeyPressed = function (e) {
        if (e.keyCode == 13) {
            return true;
        }
        return false;
    };

    this.insertMessage = function (message) {

        this.processMessage(message);

        var html = Messenger.chatMessageTemplate.replace(/\[MESSAGE\]/g, message.text);
        html = html.replace(/USER_PICTURE/, message.userImage);

        html = html.replace(/\[DATE_TIME\]/, message.dateTime);

        var status = this.getMessageStatus(message);
        html = html.replace(/\[STATUS\]/, status);

        var direction = (message.from == this.contactId) ? "in" : "out";
        html = html.replace(/\[DIRECTION\]/, direction);

        var profileId = (message.from == this.contactId) ? this.contactId : Messenger.currentUserId;
        html = html.replace(/\[PROFILE_ID\]/, profileId);

        html = html.replace(/\[MESSAGE_SECTION_ID\]/g, message.id);

        html = $.emoticons.replace(html);

        this.sentMessagesArea.append(html);
        if (this.sentMessagesArea.needsToBeScrolled()) {
            //this.sentMessagesArea.scroll();
        }

        /*
        if( && dialog && this.contactId == dialog.contactId){
            //dialog.insertMessage(message);
        }
        */

    };

    this.processMessage = function (message) {
        if (message.from == this.contactId) {
            this.allowedToReadMessage = message.allowedToRead;
        } else {
            Messenger.addUnreadMessageId(message);
        }

        message.text = (!message.text.length && !this.allowedToReadMessage)
            ? (Messenger.currentUserHasPoints)
                ? $('#paymentText').val() + ' <a href="' + $('#paymentLink').val() + '">' + $('#paymentLinkText').val() + '</a> or <a onclick="Messenger.useFreePointToReadMessage(this)" class="usePoint">' + $('#pointsLinkText').val() + '</a>'
                : $('#paymentText').val() + ' <a href="' + $('#paymentLink').val() + '">' + $('#paymentLinkText').val() + '</a>'
            : message.text.replace(/(?:(https?\:\/\/[^\s]+))/m, '<a href="$1" target="_blank">$1</a>')
        ;
    };


    this.getMessageStatus = function (message) {
        return (message.from == Messenger.currentUserId)
            ? (message.isSaved)
                ? (message.isRead)
                    ? Messenger.messageStatus.read
                    : Messenger.messageStatus.saved
                : Messenger.messageStatus.sent
            : ''
            ;
    };

    this.addMessageToWaiting = function (message) {
        this.waitingMessages.push(message);
    };

    this.blinkingStart = function () {
        $('#' + this.contactId).find('.header').addClass('blinking');
    };

    this.blinkingStop = function () {
        $('#' + this.contactId).find('.header').removeClass('blinking');
    };

    this.setMessageAsRead = function (message) {

        $.ajax({
            url: '/messenger/message/read/messageId:' + message.id + '/userId:' + Messenger.currentUserId + '/contactId:' + this.contactId,
            headers: {'apiKey': Messenger.apiKey},
            timeout: 10000,
            dataType: 'json',
            context: this,
            success: function (response, status) {
                if (response.success) {
                }
            }
        });

    };

    this.needToCloneMessage = function (message) {
        return (dialog && this.contactId == dialog.contactId) ? true : false;
    };

    this.cloneMessage = function (message) {
        dialog.insertMessage(message);
    };

    this.deleteMessage = function(elem) {

        var elem = $(elem);



        var id = elem.attr('data-id');
        elem.parents('.messageSection').remove();
        $.ajax({
            url: '/messenger/message/delete/' + id + '/' + this.contactId + '/userId:' + Messenger.currentUserId,
            headers: {'apiKey': Messenger.apiKey},
            timeout: 80000,
            dataType: 'json',
            type: 'Post',
            //data: 'userId='+Messenger.currentUserId,
            context: this,
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            success: function (res) {
                if (res) {
                    console.log(elem.parents('.messageSection'));
                }
            }
        });
    };

    this.deleteMessageInbox = function(elem) {

        var elem = $(elem);
        let contactId =  elem.attr('data-contactid');
        var id = elem.attr('data-id');
        //elem.parents('.messageSection').remove();
        elem.closest('li').hide();

        $.ajax({
            url: '/messenger/message/delete/0/' + contactId + '/userId:' + Messenger.currentUserId,
            headers: {'apiKey': Messenger.apiKey},
            timeout: 80000,
            dataType: 'json',
            type: 'Post',
            context: this,
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            success: function (res) {
                if (res) {
                }
            }
        });
    };

    this.sendMessage = function (textarea) {
        if (textarea.prop('tagName') == 'TEXTAREA') {
            var message = textarea.val().replace(/(?:\r\n|\r|\n)/g, '<br />');

            if (message.length == 0) {
                return false;
            }

            textarea.val('');
        } else {
            var message = textarea.text().replace(/(?:\r\n|\r|\n)/g, '<br />');

            if (message.length == 0) {
                return false;
            }

            textarea.html('');
        }
        var messageOptions = {
            id: Messenger.createRandomId(),
            from: Messenger.currentUserId,
            text: message,
            userImage: Messenger.currentUserImage,
            dateTime: "",
            isSaved: false,
        };

        this.insertMessage(messageOptions);

        if (this.needToCloneMessage(messageOptions)) {
            this.cloneMessage(messageOptions);
        }

        $.ajax({
            url: '/messenger/message/send/userId:' + Messenger.currentUserId + '/contactId:' + this.contactId,
            headers: {'apiKey': Messenger.apiKey},
            timeout: 80000,
            dataType: 'json',
            type: 'Post',
            data: 'message=' + encodeURIComponent(message),
            context: this,
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            error: function (response) {

            },
            success: function (response, status) {
                console.log('test' + response);
                if (response.success) {
                    var message = response.message;
                    Messenger.addUnreadMessageId(message);
                    this.updateMessageSection(messageOptions.id, message);
                    Messenger.currentUserImage = message.senderImage;

                    $('.msg_text').last().find('.trash').data(34)
                } else {

                    if (response.contactIsFrozen) {
                        alert('הודעה זו לא נשלחה כיוון שמשתמש זה הקפיא את חשבונו.');
                        return;
                    }

                    if (response.chatIsForbidden) {
                        alert("הודעה זו לא נשלחה כיוון שמשתמש זה נמצא ברשימת החסומים שלך או שאת/ה נמצא ברשימת החסומים שלו. אם את/ה מעוניין להסיר משתמש זה מרשימת החסומים שלך יש לגשת לניהול חשבון ולבחור בניהול רשימת חסומים.");
                        return;
                    }

                    if (response.isLimit) {
                        alert("הגעת לכמות המקסימלית של שליחת הודעות");
                        return;
                    }

                    /*if (response.chatIsNotActive != '0') {
                        if (response.chatIsNotActive == '1') {
                            textAlert("This message was not sent because this user has been blocked by the administrator.");
                        } else {
                            textAlert("This message was not sent because your account has been blocked by the administrator.");
                            window.location.href = '/';
                        }
                        return;
                    }*/
                }

            }
        });

    };


    this.updateMessageSection = function (sectionId, message) {
        $('#chatMessageSection_' + sectionId).addClass('chatMessageSection_' + message.id);
        $('#chatMessageSection_' + sectionId).find('.mdate').text(message.dateTime);
        $('#chatMessageSection_' + sectionId).find('.status').html(Messenger.messageStatus.saved);
        $('#chatMessageSection_' + sectionId).find('.userPicture').attr('src', message.senderImage);


        if (this.needToCloneMessage(message)) {
            $('#dialogMessageSection_' + sectionId)
                .addClass('dialogMessageSection_' + message.id, Messenger.messageStatus.saved)
                .find('.mdate')
                .text(message.dateTime)
            ;
        }
    };

}


var Messenger = {

    currentUserId: '',
    currentUserImage: '',
    currentUserHasPoints: false,
    chats: [],
    //activeSessions : [],
    templateHolder: '',
    template: {},
    chatMessageTemplate: '',
    dialogMessageTemplate: '',
    newMessagesRequest: '',
    docTitle: document.title,
    soundPlayed: false,
    messageStatus: {},
    unreadMessagesIds: [],
    readMessagesRequest: '',
    apiKey: '',


    init: function () {

        $.ajaxSetup({
            cache: false
        });

        Messenger.apiKey = $('#apiKey').val(),
            Messenger.currentUserId = $('#currentUserId').val(),
            Messenger.currentUserImage = $('#currentUserImage').val(),
            Messenger.templateHolder = $('#chatTemplate'),
            Messenger.template = {
                html: Messenger.templateHolder.html(),
                width: Messenger.templateHolder.find('.chatWindow').width(),
                header: {
                    height: Messenger.templateHolder.find('.header').height()
                }
            };

        Messenger.chatMessageTemplate = $('#chatMessageSectionTemplate').html();
        Messenger.dialogMessageTemplate = $('#dialogMessageSectionTemplate').html();

        Messenger.messageStatus = {
            sent: "sent",
            saved: "unread",
            read: "read",
        };

        if ($(window).width() < 1020) {
            //Messenger.checkNewMessagesMobile();
        } else {
            //Messenger.checkNewMessages();
        }

        if ($('#dialog, #dialog_mob').size() > 0) {
            Messenger.checkMessagesIfRead();
        }
    },

    getAllChats: function () {
        return this.chats;
    },

    getChat: function (contactId) {
        if (Messenger.chats.length > 0) {
            for (var i in Messenger.chats) {
                var chat = Messenger.chats[i];
                if (chat.contactId == contactId) {
                    return chat;
                }
            }
        }

        return false;
    },

    setChat: function (chat) {
        Messenger.chats.push(chat);
    },

    unsetChat: function (chat) {

        var index = Messenger.chats.indexOf(chat);
        if (index > -1) {
            Messenger.chats.splice(index, 1);
        }
    },

    relocateChats: function () {
        for (var i in Messenger.chats) {
            var chat = Messenger.chats[i];
            var position = Messenger.calculateChatPosition(i);
            $('.chatsArea').find('#' + chat.contactId).css({"right": position.x, "bottom": position.y});
        }
    },

    calculateChatPosition: function (i) {

        var chatsNumberInRow = Math.floor($(window).width() / (Messenger.template.width + 20));
        var currentRowIndex = Math.floor(i / chatsNumberInRow);

        var chatsNumberIncurrentRow = i - (chatsNumberInRow * currentRowIndex);
        var chatPositionX = chatsNumberIncurrentRow * Messenger.template.width + (chatsNumberIncurrentRow * 20) + 20;

        if (chatPositionX == 0) {
            chatPositionX = 20;
        }

        var chatPositionY = currentRowIndex * Messenger.template.header.height + 20;

        if (chatPositionY > 20) {
            chatPositionY += currentRowIndex * 20;
        }

        return position = {
            x: chatPositionX,
            y: chatPositionY
        };
    },


    checkNewMessages: function () {

        if ($('#apiKey').size() > 0) {
            $.ajax({
                url: '/messenger/newMessages/userId:' + Messenger.currentUserId,
                headers: {'apiKey': Messenger.apiKey},
                timeout: 80000,
                dataType: 'json',
                context: this,
                error: function (response) {

                },
                success: function (newMessages, status) {

                    if (newMessages.fromUsers.length > 0) {
                        for (var i in newMessages.fromUsers) {
                            var user = newMessages.fromUsers[i];
                            var options = {
                                contactId: user.id,
                                contactName: user.name,
                                creatingInCycle: true
                            };

                            if (!dialog || (dialog && user.id != dialog.contactId)) {
                                Lobibox.notify('success', {
                                    title: user.name,
                                    img: user.photo,
                                    msg: user.message + '<input type="hidden" value="/user/messenger/dialog/open/userId:' + Messenger.currentUserId + '/contactId:' + user.id + '">',
                                    delay: '6000',
                                    closable: false,
                                    position: 'left bottom',
                                    showClass: 'fadeInUp',
                                    hideClass: 'fadeOutDown',

                                });
                            }
                        }
                    }

                    $('.lobibox-notify').click(function () {
                        var url = $(this).find('input[type="hidden"]').val();
                        window.location.href = url;
                    });

                    setTimeout(function () {
                        Messenger.checkNewMessages();
                    }, 10000);

                }
            });
        }
    },


    checkNewMessagesMobile: function () {
        var contactId = dialog ? dialog.contactId : 0;

        $.ajax({
            url: '/messenger/newMessagesMobile/' + Messenger.currentUserId + '/' + contactId,
            headers: {'apiKey': Messenger.apiKey},
            timeout: 80000,
            dataType: 'json',
            context: this,
            error: function (response) {

            },
            success: function (response, status) {
                if (response.newMessagesNumber > 0) {
                    $('.bottom_menu .item.chat .number div').text(response.newMessagesNumber);
                    $('.bottom_menu .item.chat .number').show();
                } else {
                    $('.bottom_menu .item.chat .number').hide()
                }
                setTimeout(function () {
                    Messenger.checkNewMessagesMobile();
                }, 10000);
            }
        });
    },


    openChatsByActiveSessions: function () {


        $.ajax({
            url: '/messenger/activeChats/userId:' + Messenger.currentUserId,
            headers: {'apiKey': Messenger.apiKey},
            timeout: 80000,
            dataType: 'json',
            context: this,
            error: function (response) {
            },
            success: function (response, status) {
                var activeChats = response.activeChats;

                if (activeChats.length > 0) {

                    for (var i in activeChats) {

                        contact = activeChats[i];
                        var options = {
                            contactId: contact.id,
                            contactName: contact.name,
                            creatingInCycle: true
                        };

                        var chat = new Chat(options);
                        chat.open();
                    }
                }
            }
        });
    },


    checkMessagesIfRead: function () {

        if (Messenger.unreadMessagesIds.length > 0) {

            if (Messenger.readMessagesRequest != '')
                Messenger.readMessagesRequest.abort();

            Messenger.readMessagesRequest = $.ajax({
                url: '/messenger/checkMessagesIfRead/userId:' + Messenger.currentUserId,
                headers: {'apiKey': Messenger.apiKey},
                timeout: 80000,
                dataType: 'json',
                type: 'Post',
                data: 'messages=' + Messenger.unreadMessagesIds,
                context: this,
                error: function (response) {
                    if (response.statusText != 'abort') {
                        setTimeout(function () {
                            Messenger.checkMessagesIfRead();
                        }, 1000);
                    }
                },
                success: function (response, status) {
                    if (response.readMessages.length > 0) {
                        for (var i in response.readMessages) {
                            var messageId = response.readMessages[i];
                            $('#chatMessageSection_' + messageId)
                                .removeClass('sent')
                                .removeClass('unread')
                                .addClass(Messenger.messageStatus.read)
                            ;

                            $('#dialogMessageSection_' + messageId)
                                .removeClass('sent')
                                .removeClass('unread')
                                .addClass(Messenger.messageStatus.read)
                            ;

                            $('.chatMessageSection_' + messageId)
                                .removeClass('sent')
                                .removeClass('unread')
                                .addClass(Messenger.messageStatus.read)
                            ;

                            $('.dialogMessageSection_' + messageId)
                                .removeClass('sent')
                                .removeClass('unread')
                                .addClass(Messenger.messageStatus.read)
                            ;

                            Messenger.deleteUnreadMessageId(messageId);
                        }
                    }

                    Messenger.checkMessagesIfRead();

                }
            });

        } else {
            setTimeout(function () {
                Messenger.checkMessagesIfRead();
            }, 1000);
        }
    },

    deleteUnreadMessageId: function (messageId) {
        var index = Messenger.unreadMessagesIds.indexOf(messageId);
        if (index > -1) {
            Messenger.unreadMessagesIds.splice(index, 1);
        }
    },

    checkActiveWindowsNewMessages: function () {

        if (Messenger.getAllChats().length > 0 || dialog) {

            if (Messenger.newMessagesRequest != '')
                Messenger.newMessagesRequest.abort();

            var checkForDialogAlso = false;
            var contactId = false;

            if (dialog) {
                checkForDialogAlso = true;
                contactId = dialog.contactId;
            }

            Messenger.newMessagesRequest = $.ajax({
                url: '/messenger/activeChats/newMessages/userId:' + Messenger.currentUserId + '/contactId:' + contactId + '/' + checkForDialogAlso,
                headers: {'apiKey': Messenger.apiKey},
                timeout: 80000,
                dataType: 'json',
                type: 'Post',
                context: this,
                error: function (response) {
                    if (response.statusText != 'abort') {
                        setTimeout(function () {
                            Messenger.checkActiveWindowsNewMessages();
                        }, 1000);
                    }
                },
                success: function (response, status) {
                    if (response.newMessages.length > 0) {
                        for (var i in response.newMessages) {
                            var message = response.newMessages[i];
                            var chat = Messenger.getChat(message.from);

                            if (chat) {
                                if (dialog && chat.contactId == dialog.contactId) {
                                    dialog.insertMessage(message);
                                    if (dialog.allowedToReadMessage) {
                                        dialog.setMessageAsRead(message);
                                    }
                                }

                                chat.insertMessage(message);

                                if (chat.isMinimized) {
                                    chat.addMessageToWaiting(message);
                                    chat.blinkingStart();
                                    Messenger.blinkingTitleStart();
                                    if (!Messenger.soundPlayed) {
                                        $('body').append('<embed src="/assets/frontend/media/newMessage.mp3" autoplay="true" autostart="true" type="audio/x-wav" width="1" height="1">');
                                        Messenger.soundPlayed = true;
                                    }
                                } else {
                                    if (chat.allowedToReadMessage) {
                                        chat.setMessageAsRead(message);
                                    }
                                    chat.blinkingStop();
                                    Messenger.blinkingTitleStop();
                                }
                            } else if (dialog && message.from == dialog.contactId) {
                                Messenger.currentUserHasPoints = response.currentUserHasPoints;
                                dialog.insertMessage(message);
                                if (dialog.allowedToReadMessage) {
                                    dialog.setMessageAsRead(message);
                                }
                            } else {
                                var options = {
                                    contactId: message.from,
                                    contactName: message.userName,
                                    creatingInCycle: true
                                };

                                var chat = new Chat(options);
                                chat.open();
                            }

                        }

                    }

                    Messenger.soundPlayed = false;
                    Messenger.checkActiveWindowsNewMessages();

                }
            });

        }
    },


    setMessageAsNotified: function (messageId) {
        $.ajax({
            url: '/messenger/message/notify/messageId:' + messageId + '/userId:' + Messenger.currentUserId,
            headers: {'apiKey': Messenger.apiKey},
            timeout: 10000,
            dataType: 'json',
            context: this,
            success: function (response, status) {
                if (response.success) {
                }
            }
        });

    },

    setUnreadMessagesIds: function (chat) {
        for (var j in chat.unreadMessagesFromUser) {
            var message = chat.unreadMessagesFromUser[j];
            if (message.id != undefined && Messenger.unreadMessagesIds.indexOf(message.id) == -1 && parseInt(message.id) > 0) {
                Messenger.unreadMessagesIds.push(message.id);
            }
        }
    },

    addUnreadMessageId: function (message) {
        if (!message.isRead && message.isSaved && Messenger.unreadMessagesIds.indexOf(message.id) == -1 && parseInt(message.id) > 0) {
            Messenger.unreadMessagesIds.push(message.id);
        }
    },

    useFreePointToReadMessage: function (thisObj) {

        var messageSectionId = $(thisObj).parents('.messageSection').attr('id');
        var messageSectionIdArr = messageSectionId.split('_');
        var messageId = messageSectionIdArr[1];

        $.ajax({
            url: '/messenger/message/messageId:' + messageId + '/userId:' + Messenger.currentUserId + '/useFreePointToRead',
            headers: {'apiKey': Messenger.apiKey},
            timeout: 10000,
            dataType: 'json',
            error: function (error) {
            },
            success: function (response) {
                if (response.success) {
                    var message = $.emoticons.replace(response.message.text);
                    var messageBox = $(thisObj).parents('.message');
                    var dateTimeBlock = '<span class="mdate">' + messageBox.find('.mdate').text() + '</span>';
                    messageBox.css({'min-height': messageBox.height(), 'position': 'relative'})
                    messageBox.html('<h4>' + message + '</h4>' + dateTimeBlock);
                    var chat = Messenger.getChat(response.message.from);
                    if (chat) {
                        var currentScrollPosition = Math.abs(messageBox.parents('.overview').position().top);
                    }
                } else {
                    alert("You don't have a point to read the message");
                }
            }
        });
    },

    blinkingTitleStart: function () {
        Messenger.blinkingTitleStop();
        blinkingTitle = setInterval(function () {
            document.title = (document.title == "***New Message***" ? Messenger.docTitle : "***New Message***");
        }, 500);

    },

    blinkingTitleStop: function () {
        if (blinkingTitle) {
            clearInterval(blinkingTitle);
            document.title = Messenger.docTitle;
        }
    },


    play: function (id) {
        var sound = document.getElementById(id);
        sound.Play();
    },

    createRandomId: function () {
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

        for (var i = 0; i < 8; i++)
            text += possible.charAt(Math.floor(Math.random() * possible.length));

        return text;
    },

    emoticonsBindClickEvent: function () {
        $('.emoticons_wrapper .emoticon').unbind('click').click(function () {
            Messenger.insertEmoticonCode($(this).text(), document.getElementById('dialog_textarea'));
        });
    },

    insertEmoticonCode: function (str, textArea) {
        if ((' ' + textArea.className + ' ').indexOf(' hidden ') > -1) {
            var div = document.getElementById('dialog_textarea1');
            Messenger.setCursor(textArea, savedSelection.start);
        }
        var val = textArea.value;
        var before = val.substring(0, textArea.selectionStart);
        var after = val.substring(textArea.selectionEnd, val.length);
        textArea.value = before + str + after;
        if ((' ' + textArea.className + ' ').indexOf(' hidden ') > -1) {

            div.innerHTML = $.emoticons.replace(textArea.value.replace(/(?:\r\n|\r|\n)/g, '<br>'));//before + strHtml + after;

            savedSelection.start = savedSelection.end = savedSelection.start + str.length;
            restoreSelection(div, savedSelection);
        } else {
            Messenger.setCursor(textArea, before.length + str.length);
        }
    },

    setCursor: function (elem, pos) {
        if (elem.setSelectionRange) {
            elem.focus();
            elem.setSelectionRange(pos, pos);
        } else if (elem.createTextRange) {
            var range = elem.createTextRange();
            range.collapse(true);
            range.moveEnd('character', pos);
            range.moveStart('character', pos);
            range.select();
        }
    },

    qtipInit: function (object, template, showCallbackFunc, title) {
        object.qtip({
            events: {
                show: showCallbackFunc,
            },
            content: {
                text: template.html(),
                title: {
                    text: title,
                    button: true
                }
            },
            style: {
                classes: 'ui-tooltip-shadow ui-tooltip-rounded qtip-bootstrap',
                tip: {
                    corner: true,
                    height: 24
                }
            },
            position: {
                my: 'top right', // Use the corner...
                at: 'bottom right', // ...and opposite corner
                width: 560,
                adjust: {
                    y: -210
                },
            },
            show: {
                event: 'click'
            },
            hide: false,
        });
    }

};






