import {Controller} from "stimulus";
import Dropzone from "dropzone";

export default class extends Controller {
    connect() {
        Dropzone.options.formdrop = {
            dictDefaultMessage: "Glissez ici vos images ou cliquez sur cette zone pour ajouter des photos",
            init: function () {
                this.on("addedfile", function (file) {

                });
            }
        };
    }
}
