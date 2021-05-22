import {createRouter, createWebHistory} from "vue-router";
import MainPage from "../components/MainPage";
import User from "../components/User";
import Counter from "../components/Counter";
import Calendar from "../components/Calendar";
import List from "../components/List";
import Task from "../components/Task";

const NotFound = { template: '<div>404</div>' }

const routes = [
    { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFound },
    { path: '/', component: MainPage, meta: {title: 'Main page'}},
    { path: '/user', component: User, meta: {title: 'User'}},
    { path: '/counter', component: Counter, meta: {title: 'Count me'} },
    { path: '/calendar', component: Calendar, meta: {title: 'Calendar'} },
    { path: '/list/:parentId?', name: 'task-list', component: List, meta: {title: 'List'} },
    { path: '/task/:taskId', name: 'task-detail', component: Task, meta: {title: 'Task'} },
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
