#!/usr/bin/env python3

import sys
import pyads
import json
import simplejson
from http.server import BaseHTTPRequestHandler, HTTPServer

typeVariables = {} 
actualVariables = {} 

var_file = sys.argv[1]
#with open(var_file) as f:
#	var_data = json.load(f)
#	for key in var_data:
#		typeVariables[key] = var_data[key]['type']
#		actualVariables[key] = var_data[key]['def']

#typeVariables = {'bHallLightMain':'BOOL', 'bHallLightBack':'BOOL', 'iHallLightDimmer':'BYTE','bKitchenLightMain':'BOOL','bKitchenLightBack':'BOOL','bKitchenLightSub':'BOOL','bKitchenLightWork':'BOOL','bMainDoorSMK':'BOOL','bMainDoorLock':'BOOL','bSubDoorSMK':'BOOL','bSubDoorLock':'BOOL'}
#actualVariables = {'bHallLightMain': False, 'bHallLightBack': False, 'iHallLightDimmer': 0, 'bKitchenLightMain':False,'bKitchenLightBack':False,'bKitchenLightSub':False,'bKitchenLightWork':False,'bMainDoorSMK':False,'bMainDoorLock':False,'bSubDoorSMK':False,'bSubDoorLock':False}

plc = pyads.Connection('5.4.84.158.1.1', 801, "10.19.144.254")
plc.open()

class myHandler(BaseHTTPRequestHandler):
	def do_GET(self):
		typeVariables = {} 
		actualVariables = {} 
		with open(var_file) as f:
			var_data = json.load(f)
			for key in var_data:
				typeVariables[key] = var_data[key]['type']
				actualVariables[key] = var_data[key]['def']
		self.send_response(200)
		self.send_header('Content-type','application/json')
		self.end_headers()
		for key in actualVariables:
			if (typeVariables[key]=='BOOL'):
				value = plc.read_by_name("ADS."+key, pyads.PLCTYPE_BOOL)
			if (typeVariables[key]=='BYTE'):
				value = plc.read_by_name("ADS."+key, pyads.PLCTYPE_BYTE)
			if (typeVariables[key]=='WORD'):
				value = plc.read_by_name("ADS."+key, pyads.PLCTYPE_WORD)
			actualVariables[key] = value
		json_object = json.dumps(actualVariables, indent=4)
#		print (json_object)
		message = 'hello'
		self.wfile.write(bytes(json_object, "utf8"))
#		plc.close()
		return
	def do_POST(self):
		typeVariables = {} 
		actualVariables = {} 
		with open(var_file) as f:
			var_data = json.load(f)
			for key in var_data:
				typeVariables[key] = var_data[key]['type']
				actualVariables[key] = var_data[key]['def']
#		print("do_POST()")
		self.send_response(200)
		self.send_header('Content-type','text/html')
		self.end_headers()
		content_len = int(self.headers.get('Content-Length'))
		self.data_string = self.rfile.read(content_len)
		data = simplejson.loads(self.data_string)
		plc.write_by_name('ADS.IsBusy',True,pyads.PLCTYPE_BOOL)
		for key in actualVariables:
			if key in data:
				plc.write_by_name('ADS.IsWriting',True,pyads.PLCTYPE_BOOL)
				if (typeVariables[key]=='BOOL'):
					plc.write_by_name("ADS."+key,data[key], pyads.PLCTYPE_BOOL)
				if (typeVariables[key]=='BYTE'):
					plc.write_by_name("ADS."+key,data[key], pyads.PLCTYPE_BYTE)
				if (typeVariables[key]=='WORD'):
					plc.write_by_name("ADS."+key,data[key], pyads.PLCTYPE_WORD)
				
#				print (data[key])

		message = 'OK'
		self.wfile.write(bytes(message, "utf8"))
		plc.write_by_name('ADS.IsBusy',False,pyads.PLCTYPE_BOOL)
		return

server = HTTPServer(('127.0.0.1', 8081), myHandler)
server.serve_forever()

