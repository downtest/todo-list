import Bell from '../../../assets/icons/temp/bell.png'
import BellWhite from '../../../assets/icons/temp/bell-white.png'
import Briefcase from '../../../assets/icons/temp/briefcase.png'
import BriefcaseWhite from '../../../assets/icons/temp/briefcase-white.png'
import Calendar from '../../../assets/icons/temp/calendar.svg'
import Checklist from '../../../assets/icons/temp/checklist.png'
import Clock from '../../../assets/icons/temp/clock.svg'
import Dots from '../../../assets/icons/temp/dots.png'
import DotsWhite from '../../../assets/icons/temp/dots-white.png'
import EyeCrossed from '../../../assets/icons/temp/eye_crossed.svg'
import EyeOpened from '../../../assets/icons/temp/eye_opened.svg'
import Move from '../../../assets/icons/temp/move.png'
import Pen from '../../../assets/icons/temp/pen.png'
import PenWhite from '../../../assets/icons/temp/pen-white.png'
import Plus from '../../../assets/icons/temp/plus.svg'
import PlusWhite from '../../../assets/icons/temp/plus-white.svg'
import Profile from '../../../assets/icons/temp/profile.png'
import ProfileWhite from '../../../assets/icons/temp/profile-white.png'
import RightArrow from '../../../assets/icons/temp/right_arrow.svg'
import Search from '../../../assets/icons/temp/search.svg'
import Trash from '../../../assets/icons/temp/trash.svg'
import Undo from '../../../assets/icons/temp/undo.png'

import HeaderH1 from '../../../assets/icons/temp/contenteditable-controls/heading-h1.svg'
import CheckBox from '../../../assets/icons/temp/contenteditable-controls/checkbox.svg'
import NumberList from '../../../assets/icons/temp/contenteditable-controls/number-list.svg'

const icons = {
    namespaced: true,

    getters: {
        Bell: () => Bell,
        BellWhite: () => BellWhite,
        Briefcase: () => Briefcase,
        BriefcaseWhite: () => BriefcaseWhite,
        Calendar: () => Calendar,
        Checklist: () => Checklist,
        Clock: () => Clock,
        Dots: () => Dots,
        DotsWhite: () => DotsWhite,
        EyeCrossed: () => EyeCrossed,
        EyeOpened: () => EyeOpened,
        Move: () => Move,
        Pen: () => Pen,
        PenWhite: () => PenWhite,
        Plus: () => Plus,
        PlusWhite: () => PlusWhite,
        Profile: () => Profile,
        ProfileWhite: () => ProfileWhite,
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
    },
}

export default icons
