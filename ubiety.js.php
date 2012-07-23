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
		window_state: null,
		pusher: null,
		channel: null,
		pusher_me: null,
		pusher_socket_id: null,
		constants: {
			window_state: {
				OPEN: 1,
				CLOSED: 2
			},
			pusher: {
				app_key: '<?php echo get_option('ubiety_app_key'); ?>'
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
				if(e.keyCode == 13 && self.pusher_me != null && self.$msg.val().length != 0) {
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
		
		init: function() {
			var self = this;
			self.window_state = self.constants.window_state.CLOSED;
			self.$window = $("#ubiety-window");
			self.$bar = $("#ubiety-bar");
			self.$online_list = $("#online-list");
			self.$online_count = $("#online-count");
			self.$messages = $("#ubiety-messages");
			self.$msg = $("#msg");
			self.bindEvents();
			self.pusherInit();
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
		
		pusherHandleMessage: function(data) {
			var self = this;
			var user = self.channel.members.get(data.userid);
			self.$messages.append("<div><strong>&lt;" + user.info.display_name + "&gt;</strong>&nbsp;" + data.msg.replace("\\","") + "</div>");
			self.$messages.scrollTop(self.$messages.prop('scrollHeight'));
		},
		
		pusherInit: function() {
			var self = this;
			if(self.constants.pusher.app_key == "APP_KEY") {
				alert("You haven't configured ubiety yet.  Please go to pusher.com and register for a free API account.  Then under wordpress admin, set the app_id, app_key, and app_secret.");
				self.$bar.hide();
				return false;
			}
			Pusher.log = function(message) { self.pusherLog(message); }
			Pusher.channel_auth_endpoint = "<?php echo plugins_url('/ubiety/ubiety_xml.php?action=pusherauth'); ?>";
			self.pusher = new Pusher(self.constants.pusher.app_key);
			self.pusher.connection.bind('connected', function() {
				self.pusher_socket_id = self.pusher.connection.socket_id;
			});
			self.channel = self.pusher.subscribe('presence-ubiety');
			self.channel.bind('pusher:subscription_succeeded', function(members) {
				self.pusherSubscriptionSucceeded(members);
			});
			self.channel.bind('message', function(data) {
				self.pusherHandleMessage(data);
			});
			self.channel.bind('pusher:member_added', function(member) {
				self.pusherMemberAdded(member);
			});
			self.channel.bind('pusher:member_removed', function(member) {
				self.pusherMemberRemoved(member);
			});
			
		},
		
		pusherLog: function(msg) {
			console.log(msg);
		},
		
		pusherMemberAdded: function(m) {
			var self = this;
			self.$messages.append("<div>*** " + m.info.display_name + " has joined chat. ***</div>");
			self.$messages.scrollTop(self.$messages.prop('scrollHeight'));
			self.redrawOnline(self.channel.members);
			self.updateUserCount(self.channel.members.count);
		},
		
		pusherMemberRemoved: function(m) {
			var self = this;
			self.$messages.append("<div>*** " + m.info.display_name + " has left chat. ***</div>");
			self.$messages.scrollTop(self.$messages.prop('scrollHeight'));
			self.redrawOnline(self.channel.members);
			self.updateUserCount(self.channel.members.count);
		},
		
		pusherSubscriptionSucceeded: function(members) {
			var self = this;
			self.pusher_me = members.me;
			self.updateUserCount(members.count);
			self.redrawOnline(members);
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
			var promise = $.ajax({
				url: '<?php echo plugins_url('/ubiety/ubiety_xml.php?action=sendmsg'); ?>',
				type: 'POST',
				dataType: 'json',
				data: {
					socket_id: self.pusher_socket_id,
					msg: msg,
					userid: self.pusher_me.info.ID
				}
			});
			self.$messages.append("<div><strong>&lt;" + self.pusher_me.info.display_name + "&gt;</strong>&nbsp;" + msg + "</div>");
			self.$messages.scrollTop(self.$messages.prop('scrollHeight'));
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
		
		updateUserCount: function(count) {
			var self = this;
			self.$online_count.html(count);
		}
	};
})(jQuery);
