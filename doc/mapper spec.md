
update($item)
delete($item)
findAllFromUser($userId)
find(int $feedId, $userId)
create($item)
findAllFromFeedWithStatus($status, $feedId, $userId);

foldermapper

find($feedId, $userId)
update($folder)
delete($folder)
create($folder)

feedmapper

find($feedId, $userId)
update($folder)
delete($folder)
create($folder)
findAll()
findAllFromUser($userId)

