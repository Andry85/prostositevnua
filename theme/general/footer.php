<?php if(!defined('IN_GS')){ die('you cannot load this page directly.'); } ?>
<?php get_footer(); ?>
       <footer class="footer">
           <ul class="social">
               <li>
               		<a rel="nofollow" target="_blank" href="#" title="vk">
               			<i class="fa fa-vk"></i>
               		</a>
               	</li>
               <li>
	               	<a rel="nofollow" target="_blank" href="#" title="facebook">
	               		<i class="fa fa-facebook"></i>
	               	</a>
	            </li>
           </ul>
           <div class="logo">
               <a href="<?php get_site_url(); ?>" title="">&nbsp;</a>
           </div>
           <a class="up" href="#top" title="">&nbsp;</a>
           
           <div class="meter">
		   <noindex>


</noindex>


           </div>

		<?php   include('footer-text.php');  ?>
		
		
       </footer>
   </div>
   







<div id="popap-one" class="popap-wrap">
        <div class="popap-shadow"></div>
        <div class="popap">
            <span class="ic-cloze"></span>
            <div class="pageScroll mCustomScrollbar">
                <div class="popap-inside">
                    <div class="popap-title">
                        <div class="heading">
                            <h2>Бриф на разработку сайта</h2>
                            <ul class="contact-list">
                                <li><i class="fa fa-mobile" aria-hidden="true"></i>063)-857-63-92</li>
                                <li><a href="mailto:prostosite.vn.ua@gmail.com" title=""><i class="fa fa-envelope-o" aria-hidden="true"></i>prostosite.vn.ua@gmail.com</a></li>
                            </ul>
                        </div> 
                    </div>
                    <div class="popap-body">
                        <form id="application" action="http://prostosait.com.ua/theme/general/application.php" method="POST" name="application">
                                
                                <div class="form-block">
                                    <h2>БАЗОВАЯ ИНФОРМАЦИЯ</h2>
                                    <div class="form-row">
                                        <div class="form-col">
                                            <div class="inn">
                                                <label>Название компании:</label>
                                                <input name="name" id="applicationName" type="text" required>
                                            </div>
                                        </div>
                                        <div class="form-col">
                                            <div class="inn">
                                                <label>E-mail:</label>
                                                <input name="email" maxlength="50"  id="applicationEmail" type="email" required>
                                            </div>
                                        </div>
                                        <div class="form-col">
                                            <div class="inn">
                                                <label>Телефоны:</label>
                                                <input class="telephone" maxlength="20" name="telephone" id="applicationTelephone" type="tel">
                                            </div>
                                        </div>
                                    </div>    
                                </div>

                                <div class="form-block">
                                    <h2>ПОДРОБНАЯ ИНФОРМАЦИЯ</h2>
                                    <div class="form-row">
                                        <div class="form-col">
                                            <div class="inn">
                                                <label>Опишите суть проекта:</label>
                                                <textarea name="idea" rows="4" placeholder="Это новый сайт или редизайн существующего?"></textarea>                                                
                                            </div>
                                        </div>
                                        <div class="form-col">
                                            <div class="inn">
                                                <label>Сайты (2-3 ссылки), которые нравятся:</label>
                                                <textarea name="likes" rows="4" placeholder="Укажите что понравилось..."></textarea>                                                
                                            </div>
                                        </div>
                                        <div class="form-col">
                                            <div class="inn">
                                                <label>Сайты (2-3 ссылки), которые не нравятся:</label>
                                                <textarea name="dontlikes" rows="4" placeholder="Укажите что не понравилось"></textarea> 
                                            </div>
                                        </div>

  
                                    </div>    
                                    <div class="form-row">
                                        <div class="form-col">
                                            <div class="inn">    
                                                <div class="select-wrap">    
                                                    <label>Тип сайта:</label>
                                                    <div class="chosen-wrap">
                                                        <select name="typeofsite" class="chosen">
                                                            <option selected value="Landing Page">Landing Page</option>
                                                            <option value="Сайт - Визитка">Сайт - Визитка</option>
                                                            <!--<option value="Корпоративний сайт">Корпоративний сайт</option>
                                                            <option value="Интернет магазин">Интернет магазин</option>
                                                            -->
                                                        </select>
                                                    </div> 
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-col">
                                            <div class="inn">    
                                                <div class="select-wrap">    
                                                    <label>Основные разделы сайта:</label>
                                                    <div class="chosen-wrap">
                                                        <select name="sectionofsite[]" class="chosen" multiple>
                                                          <option value="Новости" selected>Новости</option>
                                                          <option value="Каталог продукции">Каталог продукции</option>
                                                          <option value="текстовые разделы (профиль компании, история, FAQ и так далее)">текстовые разделы (профиль компании, история, FAQ и так далее)</option>
                                                          <option value="раздел со скачиваемыми файлами или документами">раздел со скачиваемыми файлами или документами</option>
                                                          <option value="контактная информация, форма обратной связи, карта">контактная информация, форма обратной связи, карта</option>
                                                          <option value="внутренняя зона для клиентов/партнёров/сотрудников">внутренняя зона для клиентов/партнёров/сотрудников</option>
                                                        </select>
                                                    </div> 
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-col">
                                            <div class="inn">    
                                                <div class="select-wrap">    
                                                    <label>Примерное количество страниц сайта:</label>
                                                    <div class="chosen-wrap">
                                                        <select name="pages" class="chosen">
                                                            <option selected value="0">5</option>
                                                            <option value="10">10</option>
                                                            <option value="20">20</option>
                                                            <option value="30">30</option>
                                                            <option value="40">40</option>
                                                            <option value="50">50</option>
                                                            <option value="60">100</option>
                                                        </select>
                                                    </div> 
                                                </div>
                                            </div>
                                        </div>
                                    </div> 
                                    <div class="form-row">
                                        <div class="form-col">
                                            <div class="inn">    
                                                <div class="select-wrap">    
                                                    <label>Стиль сайта:</label>
                                                    <div class="chosen-wrap">
                                                        <select name="styleofsite" class="chosen">
                                                            <option selected value="Солидно">Солидно</option>
                                                            <option value="Строго">Строго</option>
                                                            <option value="Просто">Просто</option>
                                                        </select>
                                                    </div> 
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-col">
                                            <div class="inn">    
                                                <div class="select-wrap">    
                                                    <label>Языки сайта:</label>
                                                    <div class="chosen-wrap">
                                                        <select name="languiges[]" class="chosen" multiple>
                                                            <option selected value="Уркаїнська">Уркаїнська</option>
                                                            <option value="Русский">Русский</option>
                                                            <option value="English">English</option>
                                                        </select>
                                                    </div> 
                                                </div>
                                            </div>
                                        </div>
                                        
                                    </div> 
                                    
                                </div>

                                

                                <button class="btn" form="application">Отправить<span class="icon-arrowright"></span></button>

                            </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    



</body>
</html>