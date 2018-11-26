import sys

import vk_api

try:
    session = vk_api.VkApi('', '')
    session.auth()
    api = session.get_api()

    api.messages.send(random_id=140261421, user_id=140261421, message=sys.argv[1])
    print('success')
except BaseException as e:
    print(e)

print('success')