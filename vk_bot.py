# -*- coding: UTF-8 -*-

import time
import vk_api
vk = vk_api.VkApi(token = '36ade04328cb1a2f61bdc53523cf5f17d79b6d1d76edfce7db02a01dc67a6f56c1a8b1cef34b8af77fa66')
vk._auth_token()
values = {'out': 0,'count': 100,'time_offset': 60}

def write_msg(user_id, s):
    vk.method('messages.send', {'user_id':user_id,'message':s})

while True:
    response = vk.method('messages.get', values)
    if response['items']:
        values['last_message_id'] = response['items'][0]['id']
    for item in response['items']:
            write_msg(item[u'user_id'],u'Я тупой бот, поэтому не могу понять, что ты пишешь')
    time.sleep(1)
    
    response = vk.method('messages.get', values)
    if response['items']:
        values['last_message_id'] = response['items'][0]['id']
    for item in response['items']:
            write_msg(item[u'user_id'],u'Колобок повесился')
    time.sleep(1)
    
    response = vk.method('messages.get', values)
    if response['items']:
        values['last_message_id'] = response['items'][0]['id']
    for item in response['items']:
            write_msg(item[u'user_id'],u'Котики очень прикольные и мурчащие')
    time.sleep(1)
    
    response = vk.method('messages.get', values)
    if response['items']:
        values['last_message_id'] = response['items'][0]['id']
    for item in response['items']:
            write_msg(item[u'user_id'],u'Ладно, я дурак, пока')
    time.sleep(1)