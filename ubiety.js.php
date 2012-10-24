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
		$change_name: null,
		$change_name_btn: null,
		window_state: null,
		socket: null,
		members: null,
		my_id: null,
		my_name: null,
		constants: {
			window_state: {
				OPEN: 1,
				CLOSED: 2
			},
			
			opcode: {
				MESSAGE: 0,
				NAME_CHANGE: 1,
				CHANNEL_LIST: 2,
				ID_NOTIFY: 3,
				CHANNEL_JOIN: 4
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
			
			self.$change_name.keypress(function(e) {
				if(e.keyCode == 13) {
					self.$change_name_btn.trigger('click');
				}
			});
			
			self.$change_name_btn.click(function(e) {
				self.socket.send(self.constants.opcode.NAME_CHANGE + "::" + self.$change_name.val());
				self.$msg.focus();
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
			var old_name = "";
			if(data[0] == self.my_id) {
				self.my_name = data[1];
				self.$change_name.val(self.my_name);
				self.$messages.append("<div><strong>** You are now known as " + data[1] + "</strong></div>");
				self.socket.send(self.constants.opcode.CHANNEL_LIST + "::list");
				return;
			}
			for(var i = 0; i < self.members.length; i++) {
				if(self.members[i].id == data[0]) {
					old_name = self.members[i].name;
					break;
				} 
			}
			self.$messages.append("<div><strong>** " + old_name + " is now known as " + data[1] + "</strong></div>");
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
			self.$change_name = $("#change_name");
			self.$change_name_btn = $("#change_name_btn");
			self.members = Array();
			self.bindEvents();
			self.socketInit();
		},
		
		onclose: function(e) {
			var self = this;
		},
		
		onerror: function(e) {
			var self = this;
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
				case self.constants.opcode.CHANNEL_JOIN:
					self.$messages.append("<strong>*** " + data[0] + " joined</strong>");
					break;
				default:
					console.log("Unhandled opcode: " + opcode);
			}
			
			
			console.log("Message was: " + e.data);
		},
		
		onopen: function(e) {
			var self = this;
			var guest_name = "Guest_" + self.genRand();
			self.socket.send(guest_name + "::<?= session_id() ?>::" + location.host);
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
					if(member.name != self.my_name) {
						self.my_name = member.name;
						self.$change_name.val(self.my_name);
					}
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
