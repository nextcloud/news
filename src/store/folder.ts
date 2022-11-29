import axios from "@nextcloud/axios";
import { generateUrl } from "@nextcloud/router";

import { AppState, ActionParams } from 'src/store'
import { Folder } from '../types/Folder'

export const FOLDER_MUTATION_TYPES = {
  SET_FOLDERS: 'SET_FOLDERS',
  DELETE_FOLDER: 'DELETE_FOLDER'
}

export const FOLDER_ACTION_TYPES = {
  FETCH_FOLDERS: 'FETCH_FOLDERS',
  ADD_FOLDERS: 'ADD_FOLDER',
  DELETE_FOLDER: 'DELETE_FOLDER'
}

const folderUrl = generateUrl("/apps/news/folders")

export const FOLDER_ACTIONS = {
  
  async [FOLDER_ACTION_TYPES.FETCH_FOLDERS] ({ commit }: ActionParams) {
    const folders = await axios.get(
      generateUrl("/apps/news/folders")
    );

    commit(FOLDER_MUTATION_TYPES.SET_FOLDERS, folders.data.folders);
  },
  [FOLDER_ACTION_TYPES.ADD_FOLDERS] ({ commit }: ActionParams, { folder }: { folder: Folder}) {
    console.log(folder)
    axios.post(folderUrl, {folderName: folder.name}).then(
        response => commit(FOLDER_MUTATION_TYPES.SET_FOLDERS, response.data.folders)
    );
  },
  [FOLDER_ACTION_TYPES.DELETE_FOLDER] ({ commit }: ActionParams, { folder }: { folder: Folder}) {
      /**
      this.getByFolderId(folderId).forEach(function (feed) {
          promises.push(self.reversiblyDelete(feed.id, false, true));
      });
      this.updateUnreadCache();
      */
      axios.delete(folderUrl + '/' + folder.id).then(() => commit(FOLDER_MUTATION_TYPES.DELETE_FOLDER, folder))
  },
  // loadFolder({commit}) {
  //     console.log('loading folders')
  //     axios.get(folderUrl).then(
  //         response => {
  //             commit('addFolders', response.data.folders);
  //             axios.get(feedUrl).then(
  //                 response => commit('addFeeds', response.data.feeds)
  //             )
  //         }
  //     )
  // },
}


export const FOLDER_MUTATIONS = {
  [FOLDER_MUTATION_TYPES.SET_FOLDERS] (state: AppState, folders: Folder[]) {
    folders.forEach(it => {
      it.feedCount = 0
      it.feeds = []
      state.folders.push(it)
    })
  },
  [FOLDER_MUTATION_TYPES.DELETE_FOLDER] (state: AppState, folder: Folder) {
    const index = state.folders.indexOf(folder);
    state.folders.splice(index, 1);
  }
}