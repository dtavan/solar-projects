<template>
    <nav class="navbar" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <a class="navbar-item" href="/">
                <img src="https://observer.getpylon.com/img/pylon-logo.png" height="28" width="auto">
            </a>

            <a role="button" class="navbar-burger burger" aria-label="menu" aria-expanded="false"
               data-target="navbarBasicExample">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>

        <div id="navbarBasicExample" class="navbar-menu">
            <div class="navbar-start">
                <router-link class="navbar-item" to="/projects">
                    Projects
                </router-link>

                <router-link class="navbar-item" to="/contacts">
                    Contacts
                </router-link>

                <div class="navbar-item has-dropdown is-hoverable">
                    <a class="navbar-link">
                        About the task
                    </a>

                    <div class="navbar-dropdown">
                        <a href="https://gitlab.com/pylon/coding-challenge/blob/master/readme.md" target="_blank"
                           class="navbar-item">
                            Documentation
                        </a>
                        <a href="https://gitlab.com/pylon/coding-challenge" target="_blank" class="navbar-item">
                            Get the code
                        </a>
                        <hr class="navbar-divider">
                        <a href="mailto:daniel@getpylon.com" class="navbar-item">
                            Report an issue
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>

<style>
    .navbar {
        border-bottom: 1px solid #eaeaea;
    }

    .navbar-item.router-link-active {
        border-bottom: 2px solid #3273dc;
    }
</style>
