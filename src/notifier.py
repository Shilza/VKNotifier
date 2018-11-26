import sys

import vk_api

try:
    session = vk_api.VkApi('', '')
    session.auth()
    api = session.get_api()

    username = "Username: " + sys.argv[1]
    api.messages.send(user_id=140261421, message= username + "\n" + sys.argv[2])
    print('success')
except BaseException as e:
    print(e)

print('success')