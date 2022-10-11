import {createRouter, createWebHistory} from "vue-router";
import Collections from "../components/Collections";
import User from "../components/User/User";
import MonthLayout from "../components/Calendar/Month/MonthLayout";
import Day from "../components/Calendar/Day"
import List from "../components/List";
import Task from "../components/Item/Task";
import Registration from "../components/User/Registration";
import PasswordForget from "../components/User/PasswordForget";
import PasswordReset from "../components/User/PasswordReset";
import Oauth from "../components/External/Oauth";
import store from '../store';

const NotFound = { template: '<div>404</div>' }
const ROUTER_TITLE_PREFIX = store.getters['constants/titlePrefix']
const routes = [
    {path: '/:pathMatch(.*)*', name: 'not-found', component: NotFound },
    {path: '/user', name: 'profile', component: User, meta: {title: ROUTER_TITLE_PREFIX+'User'}},
    {path: '/registration', name: 'registration', component: Registration, meta: {title: ROUTER_TITLE_PREFIX+'Registration'}},
    {path: '/password-forget', name: 'password-forget', component: PasswordForget, meta: {title: ROUTER_TITLE_PREFIX+'Password forget'}},
    {path: '/password-reset', name: 'password-reset', component: PasswordReset, meta: {title: ROUTER_TITLE_PREFIX+'Password reset'}},
    {path: '/calendar/month/:month?', name: 'calendarMonth', component: MonthLayout, meta: {title: ROUTER_TITLE_PREFIX+'Calendar'}, props: (route) => ({ month: /^\d{4}\-\d{2}$/.test(route.params.month) ? route.params.month : undefined }) },
    {path: '/calendar/day/:day', name: 'calendarDay', component: Day, meta: {title: ROUTER_TITLE_PREFIX+'Day'}, props: (route) => ({ day: /^\d{4}\-\d{2}\-\d{2}$/.test(route.params.day) ? route.params.day : undefined }) },
    {path: '/collections', name: 'collections', component: Collections, meta: {title: ROUTER_TITLE_PREFIX+'Collections'} },
    {path: '/list/:collectionId?', alias: '/', name: 'task-list', component: List, meta: {title: ROUTER_TITLE_PREFIX+'Notes'}, props: true },
    {path: '/item/:itemId?', name: 'task-item', component: Task, meta: {title: ROUTER_TITLE_PREFIX}, props: true },
    {path: '/external/oauth/:service', name: 'external-oauth', component: Oauth, meta: {title: ROUTER_TITLE_PREFIX+'Oauth verify'}, props: true },
]

const router = createRouter({
    history: createWebHistory(),
    linkActiveClass: 'link__active',
    routes, // short for `routes: routes`
})

router.beforeEach((to, from) => {
    document.title =  to.meta.title;
})

export default router;
