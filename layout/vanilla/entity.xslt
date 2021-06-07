<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:template name="entity">
    <div class="card" style="margin-bottom:1em;">
      <xsl:attribute name="id">
        <xsl:value-of select="@fqn"/>
      </xsl:attribute>
      <div class="card-body">
        <h2 class="card-title">
          <a>
            <xsl:attribute name="href">
              <xsl:text>#</xsl:text>
              <xsl:value-of select="@fqn"/>
            </xsl:attribute>
            <xsl:value-of select="@fqn"/>
          </a>
        </h2>

        <!-- PARENTS -->
        <xsl:if test="extends|implements|uses">
          <table class="table table-sm table-bordered">
            <xsl:if test="extends">
              <tr>
                <td width="1">Extends</td>
                <td>
                  <xsl:call-template name="list">
                    <xsl:with-param name="items" select="extends"/>
                  </xsl:call-template>
                </td>
              </tr>
            </xsl:if>
            <xsl:if test="implements">
              <tr>
                <td width="1">Implements</td>
                <td>
                  <xsl:call-template name="list">
                    <xsl:with-param name="items" select="implements/interface"/>
                  </xsl:call-template>
                </td>
              </tr>
            </xsl:if>
            <xsl:if test="uses">
              <tr>
                <td width="1">Uses</td>
                <td>
                  <xsl:call-template name="list">
                    <xsl:with-param name="items" select="uses/trait"/>
                  </xsl:call-template>
                </td>
              </tr>
            </xsl:if>
          </table>
        </xsl:if><!-- /PARENTS -->

        <xsl:value-of select="docblock" disable-output-escaping="yes"/>
        <xsl:call-template name="links"/>
        <xsl:call-template name="todo"/>

        <xsl:call-template name="constants"/>
        <xsl:call-template name="properties"/>
        <xsl:call-template name="methods"/>

      </div><!-- /card-body -->
    </div><!-- /card -->
  </xsl:template>
</xsl:stylesheet>