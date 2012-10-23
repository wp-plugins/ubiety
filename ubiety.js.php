<?php
require_once('ubiety_config.php');
@header("Cache-Control: no-cache, must-revalidate");
@header("Pragma: no-cache");
@header("Content-type: text/javascript");
if(!session_id()) { @session_start(); }
global $current_user;
get_currentuserinfo();
?>
//begin JS

WEB_SOCKET_FORCE_FLASH = true;
WEB_SOCKET_SWF_LOCATION = "<?= plugins_url('web-socket-js/WebSocketMain.swf', __FILE__); ?>";
WEB_SOCKET_DEBUG = true;
if(!window.console) {
	window.console = {
		log: function() {}
	};
}

(function($) {
	$(document).ready(function() {
		window.ubiety = new Ubiety();
	});

	window.Ubiety = function(options) {
		var self = this;
		self.init();
	};

	window.Ubiety.prototype = {
		$window: null,
		$bar: null,
		$online_list: null,
		$online_count: null,
		$messages: null,
		$msg: null,
		window_state: null,
		socket: null,
		members: null,
		my_id: null,
		constants: {
			window_state: {
				OPEN: 1,
				CLOSED: 2
			},
			
			opcode: {
				MESSAGE: 0,
				NAME_CHANGE: 1,
				CHANNEL_LIST: 2,
				ID_NOTIFY: 3
			}
		},
		
		bindEvents: function() {
			var self = this;
			$("#open-online").click(function(e) {
				self.toggleWindow();
			});
			
			$("#closeX").click(function(e) {
				self.toggleWindow();
			});
			
			self.$msg.keypress(function(e) {
				if(e.keyCode == 13 && self.$msg.val().length != 0) {
					self.sendMessage(self.$msg.val());
					self.$msg.val('');
				}	
			});
		},
		
		closeWindow: function() {
			var self = this;
			self.$window.hide();
			self.window_state = self.constants.window_state.CLOSED;
		},
		
		genRand: function() {
			var self = this;
			var length = 5;
			var rand = -1;
			var str = "";
			while(length) {
				while(!(rand > 96 && rand < 123) && !(rand > 64 && rand < 91)) {
					var rand = Math.round(Math.random() * 1000);
					rand = (rand % 58) + 65;
				}
				str += String.fromCharCode(rand);
				rand = -1;
				length--;
			}
			return str;
		},
		
		handleMessage: function(data) {
			var self = this;
			var member = null;
			for(var i = 0; i < self.members.length; i++) {
				if(self.members[i].id == data[0]) {
					member = self.members[i];
					break;
				}
			}
			if(member == null) {
				member = {name: 'Unknown', id: -1}; //wtf
			}
			self.$messages.append("<div><strong>&lt;" + member.name + "&gt;</strong>&nbsp;" + data[1] + "</div>");
			self.$messages.scrollTop(self.$messages.prop('scrollHeight'));
			
		},
		
		handleNameChange: function(data) {
			var self = this;
			self.socket.send(self.constants.opcode.CHANNEL_LIST + "::list");
		},
		
		init: function() {
			var self = this;
			self.window_state = self.constants.window_state.CLOSED;
			self.$window = $("#ubiety-window");
			self.$bar = $("#ubiety-bar");
			self.$online_list = $("#online-list");
			self.$online_count = $("#online-count");
			self.$messages = $("#ubiety-messages");
			self.$msg = $("#msg");
			self.members = Array();
			self.bindEvents();
			self.socketInit();
		},
		
		onclose: function(e) {
			var self = this;
			console.log("onclose fired");
		},
		
		onerror: function(e) {
			var self = this;
			console.log("onerror fired");
		},
		
		onmessage: function(e) {
			var self = this;
			var data = e.data.split("::");
			var opcode = parseInt(data[0], 10);
			data.splice(0,1);
			switch(opcode) {
				case self.constants.opcode.MESSAGE:
					self.handleMessage(data);
					break;
				case self.constants.opcode.NAME_CHANGE:
					self.handleNameChange(data);
					break;
				case self.constants.opcode.CHANNEL_LIST:
					self.updateChannelList(data[0].split("||"));
					break;
				case self.constants.opcode.ID_NOTIFY:
					self.my_id = parseInt(data, 10);
					break; 
				default:
					console.log("Unhandled opcode: " + opcode);
			}
			
			
			console.log("Message was: " + e.data);
		},
		
		onopen: function(e) {
			console.log("onopen fired");
			var self = this;
			var guest_name = "Guest_" + self.genRand();
			self.socket.send(guest_name + "::" + self.genRand() + "::" + location.host);
		},

		openWindow: function() {
			var self = this;
			self.$window.css("display", "block");
			var top = self.$bar.position().top - self.$window.height() - $(window).scrollTop();
			var left = $(window).width() - self.$window.width();
			self.$window.css("top", top+"px");
			self.$window.css("left", left+"px");
			self.$msg.focus();
			self.window_state = self.constants.window_state.OPEN;
		},
		
		redrawOnline: function(members) {
			var self = this;
			self.$online_list.html('');
			self.$online_list.append('<ul/>');
			members.each(function(m) {
				var strong = m.info.ID == self.pusher_me.info.ID ? true : false;
				self.$online_list.find('ul').append('<li>' + (strong ? "<strong>" : "") + m.info.display_name + (strong ? "</strong>" : "") + '</li>');
				self.$online_list.scrollTop(self.$online_list.prop('scrollHeight'));
			});
		},
		
		sendMessage: function(msg) {
			var self = this;
			msg = msg.replace("<", "&lt;");
			msg = msg.replace(">", "&gt;");
			self.socket.send(self.constants.opcode.MESSAGE + "::" + msg);
		},
		
		socketInit: function() {
			var self = this;
			console.log("Initting webSocket.");
			self.socket = new WebSocket("ws://ubiety.net:8080");
			self.socket.onopen = function(e) {
				self.onopen(e);
			};
			self.socket.onmessage = function(e) {
				self.onmessage(e);
			};
			self.socket.onclose = function(e) {
				self.onclose(e);
			};
			self.socket.onerror = function(e) {
				self.onerror(e);
			};

			
		},
		
		toggleWindow: function() {
			var self = this;
			switch(self.window_state) {
				case self.constants.window_state.CLOSED:
					//closed window... so open it
					self.openWindow();
					break;
				case self.constants.window_state.OPEN:
					//window open.. tear it down..
					self.closeWindow();
					break;
			}
		},
		
		updateChannelList: function(arr) {
			var self = this;
			self.members = new Array();
			var $ul = $('<ul/>');
			for(var i = 0; i < arr.length; i++) {
				var member_data = arr[i].split(",");
				var member = {
					id: member_data[0],
					name: member_data[1]
				};
				self.members.push(member);
				var $li = $('<li/>').html(member.name);
				if(member.id == self.my_id) {
					$li.css("font-weight", "bold");
				}
				$ul.append($li);
			}
			self.$online_list.html('');
			self.$online_list.append($ul);
			self.$online_count.html(self.members.length);
		},
		
		updateUserCount: function(count) {
			var self = this;
			self.$online_count.html(count);
		}
	};
})(jQuery);
