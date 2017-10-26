// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Javascript for the quizaccess_useripmapping plugin.
 *
 * @package    quizaccess_useripmapping
 * @author     Amrata Ramchandani <ramchandani.amrata@gmail.com>
 * @copyright  2017 Indian Institute Of Technology,Bombay,India
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery'], function($) {
    return {
        init: function() {

            $('form input[name=username]').val('');

            $("#userlist").on("click", "li", function() {
                $("#id_username").val($(this).text());
                $("#userlist").hide();
            });

            $("#id_username").keyup(function() {
                $.ajax({
                    type: "POST",
                    url: "suggestusernames.php",
                    data: 'keyword=' + $(this).val(),
                    beforeSend: function() {
                        $("#id_username").css("background", "#FFF url(LoaderIcon.gif) no-repeat 165px");
                    },
                    success: function(data) {
                        $("#userlist").show();
                        $("#userlist").html(data);
                        $("#id_username").css("background", "#FFF");
                    }
                });
            });

        },
        editip: function() {

            $('td').focus(function() {
                $(this).find('.fa-pencil').hide();
            });

            $('td').keydown(function(e) {
                if (e.keyCode == 13 || e.keyCode == 9) {
                    var quizid = $(this).closest('tr').find('td:eq(6)').text();
                    var username = $(this).closest('tr').find('td:eq(2)').text();
                    var value = $(this).text();
                    var url = "updateip.php";
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: {
                            quizid: quizid,
                            username: username,
                            ip: value
                        }
                    });
                    $(this).blur();
                    $('.fa-pencil').show();
                }
            });

        }

    };

});