
/**
 * First we will load all of this project's JavaScript dependencies which
 * include Vue and Vue Resource. This gives a great starting point for
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';
import 'datatables.net-bs4';
import { library, dom } from '@fortawesome/fontawesome-svg-core'
import tinymce from 'tinymce/tinymce';
import 'tinymce/themes/modern/theme';

import {
  faBook,
  faBuilding,
  faBullhorn,
  faCar,
  faCheckCircle,
  faCircle,
  faCircleNotch,
  faClipboardCheck,
  faCloud,
  faCogs,
  faComment,
  faCommentAlt,
  faCrosshairs,
  faDesktop,
  faDotCircle,
  faEnvelope,
  faExpandAlt,
  faExternalLink,
  faEye,
  faGlobe,
  faHome,
  faIdCard,
  faImage,
  faIndustry,
  faMinus,
  faPencil,
  faPlus,
  faQuestionCircle,
  faRepeat,
  faRocket,
  faSignIn,
  faSignOut,
  faStickyNote,
  faStopCircle,
  faTag,
  faTable,
  faTachometer,
  faTrashAlt,
  faUser,
  faUserCircle,
  faUserPlus,
  faUsers,
} from '@fortawesome/pro-light-svg-icons'

import {
  faFacebook,
  faTwitter,
  faTeamspeak,
} from '@fortawesome/free-brands-svg-icons'

library.add(
  faBook,
  faBuilding,
  faBullhorn,
  faCar,
  faCheckCircle,
  faCircle,
  faCircleNotch,
  faClipboardCheck,
  faCloud,
  faCogs,
  faComment,
  faCommentAlt,
  faCrosshairs,
  faDesktop,
  faDotCircle,
  faEnvelope,
  faExpandAlt,
  faExternalLink,
  faEye,
  faGlobe,
  faHome,
  faIdCard,
  faImage,
  faIndustry,
  faMinus,
  faPencil,
  faPlus,
  faQuestionCircle,
  faRepeat,
  faRocket,
  faSignIn,
  faSignOut,
  faStickyNote,
  faStopCircle,
  faTag,
  faTable,
  faTachometer,
  faTrashAlt,
  faUser,
  faUserCircle,
  faUserPlus,
  faUsers
)

library.add(
  faFacebook,
  faTwitter,
  faTeamspeak
)

dom.watch()

