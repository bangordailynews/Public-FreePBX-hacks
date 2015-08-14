# Public-FreePBX-hacks

We're big fans of Asterisk and FreePBX, but the documentation and user interface can be lacking. So we're documenting some of the things we're doing to make the system a little easier to use.

These are kinda hacky. If somebody has good documentation on a FreePBX PHP API let us know!

## Some of our tricks include
* **global-provisioning.php**: We got tired of having (and forgetting) to set the same variables in half a dozen different templates, and the Endpoint Manager doesn't actually expose all the variables you could have access to. Our global provisioning dropin allows us to identify variables we want to use globally and gives us the ability to set variables that the Endpoint Manager doesn't yet support
* **pickup-groups.php**: Pickup and call groups (see below for an explanation) are hard to manage. So this simple page helps us visualize those groups.
* **directory**: We wanted to auto-generate a directory and push it out to everybody's phones. With the help of the the global provisioning script, we can do that.


Below is a general overview of the Asterisk/FreePBX system.

# General schema

![](http://i.imgur.com/0zGcBj5.jpg)

## Inbound Routes
When a call comes in from SIP, the phone system attempts to match it against an Inbound Route. The route will define how the call is handled — should we ring an extension, send the caller to a phone tree, route him or her to a call queue, or just hang up on them?

Inbound Routes can be accessed under the Connectivity menu. The key fields are:
* Description: The plain-text name for the number. Generally we use this field to describe the location of the number.
* DID number: The full, 10-digit phone number. (e.g. 2079908000)
* Set Destination: What app will this call be handled by? Default is Main IVR, but you could choose to send the calls to an extension, a queue, the Time Condition app (so calls go to A at certain times and B at other times), etc.

## Extensions
Extensions have a one-to-one relationship with phones (even virtual phones), as well as a one-to-one relationship with voicemail boxes. Phones are registered to extensions using the MAC address off the phone.

Almost all extensions are Generic SIP devices

Key fields:
* User Extension: The internal extension to dial somebody from inside the system. Generally the last four of their DID. This actually doesn't have really any bearing on anything other than internal dialing.
* Display Name: The name to display on caller ID
* Outbound CID: The full 10-digit number to display on Caller ID when calling out of the system (e.g. 2079908000). Generally this is the same as the corresponding inbound DID, but you can really make it anything you wish. If it's not set correctly, outbound calls might not work.
* Secret: This is basically the password to register the phone in the system. Should be very secure! The default is fine, though.
* On-Demand Recording (under Recording Options): Enable
* Voicemail: Enable. Set the voicemail password to something _other_ than their extension. The email address will be used to send the person a notification when he or she has a new voicemail. Check yes on all the boxes, except Delete Voicemail.

The fields at the bottom, under Xorcom IP Phone Manager, don't work if you're trying to add a MAC address to the extension, but you want to be sure that the correct MAC address is indeed there. Without a MAC there, there may be problems pushing updates to the phones.

## IVRs
An IVR is simply a phone tree. Located under Applications.

## Queues
Queues are collections of extensions that calls can ring to, to allow distributed answering. Located under Applications.

Sometimes we chain queues. For example, the classifieds queue flows into the classifieds overflow queue if all the agents in the classifieds queue are busy or no agents in the classifieds queue are logged in. This way, we can have randomize how agents receive calls in the first queue but still maintain some agents that won't receive calls unless there's no other option.

Key Fields:
* Queue Number: Internal extension for reaching the queue
* CID Name Prefix: Allows the agent to identify which queue incoming calls are coming from.
* Static Agents: These agents will always have their phones rung.
* Dynamic Agents: These agents can log in and out of the queue, so they will only receive calls if they are at their desk, for example.
* Restrict Dynamic Agents: Always Yes. Otherwise, other people might accidentally log in to the queue.
* Ring Strategy: Pretty good descriptions in the help bubble. One note: weighting doesn't work very well.
* Skip busy agents: Generally yes. Don't try to ring agents who are on the phone.
* Music on Hold Class: Ring Only if the Max Wait Time is 30 seconds or less (about five rings). Else, Agent Ringing.
* Join Announcement: If we are forcing call recording, we must play QUALITYNEW when the person joins the queue.
* Call Recording: If yes, see above.
* Recording Mode: Generally, after answered.
* Mark calls answered elsewhere: Generally checked yes. So that every time you don't answer a call it doesn't show as a missed call on your phone.
* Max Wait Time: How long will the agents phones ring until the caller is directed to the failover?
* Agent Timeout: How long should each agent's phone ring? You probably want the multiple of the number of agents by the agent timeout to be around the max wait time. Usually only enough time for a couple of rings is sufficient.
* Leave Empty: No for static agents. For dynamic agents, Strict. So that if all agents are on the phone, rather than placing the person on hold, send them to the failover.
* Fail Over destination: Where should the caller be sent if the queue is full, timeout is hit, etc.

## Call Groups and Pickup Groups
A call group is a group of extensions that can be answered remotely.

A pickup group is a group of extensions that can answer a call group remotely.

These options are set under an extension's settings. Both values are numeric, with a maximum value of 60.

So, for example: Extensions a, b, c and d are in call group one. Extension d is in pickup group 1. Extension d can pickup calls when extensions a, b, c and d are ringing by lifting the handset and pressing *8, but extensions a, b and c can not pickup calls to extension d. If extensions a, b and c are added to pickup group 1, everybody can pick up everybody else's calls.

## Other Apps
DISA: Just don't use this. Make sure it's disabled.
Directory: The Directory application allows you to create a dial-by-name directory. **New extensions are not automatically added to the directory!**
Follow Me: A more advanced version of call forwarding that allows you to have a call ring multiple phones, internal and external, at the same time, for a defined length of time. You can also have it ring the primary phone for a certain number of seconds first.

## Outbound Routes
Outbound Routes control how external calls are handled. They support what is essentially RegEx for recognizing call patterns and then handing the call to your service provider.

## Endpoint Manager and Phone Templates
Under settings. The best and easiest way to manage provisioning of phones.

Phones are managed via MAC address. If the MAC address of the phone already exists in the system, it won't allow you to register it and it won't give you an error! If you try to apply the same extension to two phones, it will silently fail on one of them!

To register a new, never-used phone in the system, first do a Cmd-F to make sure it's not listed. At the bottom of the page, click additional device and enter the MAC (no colons) of the phone, select the brand and model, and then select a template and extension to apply to the phone. Press apply, then check the settings for that extension to make sure the MAC is listed.

You can edit and add new templates using the box to the right. The templates allow you to set settings that will be sent to the phone when the phone is booting up and requesting an IP address from the DHCP server. Mostly, the templates are used to define the buttons that will appear on the phone and the admin password. There are global Yealink settings saved on the server in /var/www/html/hacks/global-provisioning.php. If you want to make a change that will affect all phones, make it there so you don't have to maintain the setting among different templates.
