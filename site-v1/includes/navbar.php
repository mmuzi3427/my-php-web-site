            <section class="section__navigation">
                <div class="navigation__block">
                    <div onclick="window.location.href='index.php'" class="navigationBlock__item <?= $main ?>">
                        <div class="BlogItem__icon">
                            <i class="fa-solid fa-house BlogItem__img"></i>
                        </div>
                        <div class="BlogItem__description">
                            <p>Home</p>
                        </div>
                    </div>
                    
                    <div onclick="window.location.href='about.php'" class="navigationBlock__item <?= $about; ?>">
                        <div class="BlogItem__icon">
                            <i class="fa-solid fa-address-card BlogItem__img "></i>
                        </div>
                        <div class="BlogItem__description">
                            <p>About</p>
                        </div>
                    </div>
                    
                    <div onclick="window.location.href='contact.php'" class="navigationBlock__item <?= $contact; ?>">
                        <div class="BlogItem__icon">
                            <i class="fa-solid fa-phone BlogItem__img"></i>
                        </div>
                        <div class="BlogItem__description">
                            <p>Contact</p>
                        </div>
                    </div>
                    
                    <div onclick="window.location.href='blogs.php'" class="navigationBlock__item <?= $blogs; ?> oxirgi">
                        <div class="BlogItem__icon">
                            <i class="fa-solid fa-message BlogItem__img"></i>
                        </div>
                        <div class="BlogItem__description">
                            <p>Blogs</p>
                        </div>
                    </div>
                    

                    
                </div>
            </section>