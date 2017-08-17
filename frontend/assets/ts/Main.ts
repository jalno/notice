import * as $ from "jquery";
import Note from "./classes/Note";
import View from "./classes/View";
$(function(){
	Note.initIfNeeded();
	View.initIfNeeded();
});