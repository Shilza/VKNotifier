import sys

import vk_api

try:
    session = vk_api.VkApi('', '')
    session.auth()
    api = session.get_api()

    username = "Username: " +  sys.argv[1]
    who = "Who: " + sys.argv[2]
    message = username + "\n" + who + "\n" + sys.argv[3] + ": " + sys.argv[4]
    api.messages.send(user_id=140261421, message=message)
except BaseException as e:
    print(e)