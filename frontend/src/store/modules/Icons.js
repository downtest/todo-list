import Bell from '../../../assets/icons/user/bell.svg'
import BellWhite from '../../../assets/icons/temp/bell-white.png'
import Briefcase from '../../../assets/icons/temp/briefcase.png'
import BriefcaseWhite from '../../../assets/icons/temp/briefcase-white.png'
import Burger from '../../../assets/icons/menu/burger.svg'
import Calendar from '../../../assets/icons/menu/calendar-2.svg'
import CalendarWhite from '../../../assets/icons/menu/calendar-2-white.svg'
import Checklist from '../../../assets/icons/menu/checkbox-list.svg'
import ChecklistWhite from '../../../assets/icons/menu/checkbox-list-white.svg'
import Clock from '../../../assets/icons/temp/clock.svg'
import Dots from '../../../assets/icons/menu/dots-fatter.svg'
import DotsWhite from '../../../assets/icons/menu/dots-fatter-white.svg'
import EyeCrossed from '../../../assets/icons/temp/eye_crossed.svg'
import EyeOpened from '../../../assets/icons/temp/eye_opened.svg'
import Logo from '../../../public/listodo-logo-full.v1.svg'
import Move from '../../../assets/icons/temp/move.png'
import Pen from '../../../assets/icons/temp/pen.png'
import PenWhite from '../../../assets/icons/temp/pen-white.png'
import Plus from '../../../assets/icons/menu/plus.svg'
import PlusWhite from '../../../assets/icons/menu/plus-white.svg'
import Profile from '../../../assets/icons/menu/profile.svg'
import ProfileWhite from '../../../assets/icons/menu/profile-white.svg'
import NotesCloud from '../../../assets/icons/notes/cloud.svg'
import NotesCloudCrossed from '../../../assets/icons/notes/cloud-crossed.svg'
import NotesCloudCrossedNo2 from '../../../assets/icons/notes/cloud-crossed-no2.svg'
import NotesPen from '../../../assets/icons/notes/pen.svg'
import NotesPenWithLine from '../../../assets/icons/notes/pen-with-line.svg'
import RightArrow from '../../../assets/icons/temp/right_arrow.svg'
import Search from '../../../assets/icons/temp/search.svg'
import Trash from '../../../assets/icons/list/trash.svg'
import Undo from '../../../assets/icons/temp/undo.png'

import HeaderH1 from '../../../assets/icons/temp/contenteditable-controls/heading-h1.svg'
import CheckBox from '../../../assets/icons/temp/contenteditable-controls/checkbox.svg'
import NumberList from '../../../assets/icons/temp/contenteditable-controls/number-list.svg'

import VK from '../../../assets/icons/oauth/vk.svg'

const icons = {
    namespaced: true,

    getters: {
        Bell: () => Bell,
        BellWhite: () => BellWhite,
        Briefcase: () => Briefcase,
        BriefcaseWhite: () => BriefcaseWhite,
        Burger: () => Burger,
        Calendar: () => Calendar,
        CalendarWhite: () => CalendarWhite,
        Checklist: () => Checklist,
        ChecklistWhite: () => ChecklistWhite,
        Clock: () => Clock,
        Dots: () => Dots,
        DotsWhite: () => DotsWhite,
        EyeCrossed: () => EyeCrossed,
        EyeOpened: () => EyeOpened,
        Logo: () => Logo,
        Move: () => Move,
        Pen: () => Pen,
        PenWhite: () => PenWhite,
        Plus: () => Plus,
        PlusWhite: () => PlusWhite,
        Profile: () => Profile,
        ProfileWhite: () => ProfileWhite,
        NotesCloud: () => NotesCloud,
        NotesCloudCrossed: () => NotesCloudCrossed,
        NotesCloudCrossedNo2: () => NotesCloudCrossedNo2,
        NotesPen: () => NotesPen,
        NotesPenWithLine: () => NotesPenWithLine,
        RightArrow: () => RightArrow,
        Search: () => Search,
        Trash: () => Trash,
        Undo: () => Undo,
        ContenteditableControls() {
            return {
                H1: HeaderH1,
                CheckBox: CheckBox,
                NumberList: NumberList,
            }
        },
        Oauth() {
            return {
                vk: VK,
            }
        },
    },
}

export default icons
