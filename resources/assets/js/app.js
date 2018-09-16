
/**
 * First we will load all of this project's JavaScript dependencies which
 * include Vue and Vue Resource. This gives a great starting point for
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';
import 'datatables.net-bs4';
import { library, dom } from '@fortawesome/fontawesome-svg-core'

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
  faCrosshairs,
  faDesktop,
  faDotCircle,
  faEnvelope,
  faExpandAlt,
  faExternalLink,
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
  faCrosshairs,
  faDesktop,
  faDotCircle,
  faEnvelope,
  faExpandAlt,
  faExternalLink,
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
